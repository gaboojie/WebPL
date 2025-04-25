// Instantiate global variables
let graph = null;
let mode = null;
let lastNodeClicked = null;
let selectedEdge = null;
let selectedNode = null;
let directed = false;
let useSmoothEdges = false;
let runMode = "Stopped";
let worker = null;
let editor = null;
let lastLineHighlighted = null;

// Define template nodes and edges
let nodes = [];
let edges = [];

// Initialize function (after DOM loads)
function initialize() {
    // Create data sets to modify DOM when they get updated
    nodes = new vis.DataSet(nodes);
    edges = new vis.DataSet(edges);
    var data = {
        nodes: nodes,
        edges: edges
    };
    var options = {
        physics: false,
        edges: {
            arrows: {
                to: { enabled: directed, type: 'arrow' }
            },
            smooth: useSmoothEdges
        }
    };

    // Create graph
    var container = document.getElementById("graph");
    graph = new vis.Network(container, data, options);

    // Add listeners
    addGraphButtonListeners();

    // Initially disable graph input
    disableGraphInput();

    // Add ACE support
    editor = ace.edit("editor");
    editor.setTheme('ace/theme/github')
    editor.session.setMode("ace/mode/javascript");

    // Allow for newer feature support
    editor.session.on('changeMode', function(e, session) {
        if (session.getMode().$id === "ace/mode/javascript" && session.$worker) {
            session.$worker.send("setOptions", [{
                esversion: 8,
                esnext: false
            }]);
        }
    });
}

// Initialize JS to load after DOM loads
window.onload = function () {
    initialize();
};

// Add button toggles for graph buttons
function addGraphButtonListeners() {
    // Add node listener
    $('#add').on('click', () => {
        // Toggle mode
        mode = (mode === "Add") ? null : "Add";
        updateGraphButtons();
    });

    // Remove node listener
    $('#remove').on('click', () => {
        // Toggle mode
        mode = (mode === "Remove") ? null : "Remove";
        updateGraphButtons();
    });

    // Smooth edge listener
    const smoothElement = document.getElementById('smoothEdge');
    smoothElement.addEventListener('click', function() {
        // Toggle if we're using smooth edges
        useSmoothEdges = !useSmoothEdges;
        const options = {
            edges: {
                arrows: {
                    to: {
                        enabled: directed, type: 'arrow'
                    }
                },
                smooth: useSmoothEdges
            }
        };
        graph.setOptions(options);
        if (useSmoothEdges) {
            smoothElement.textContent = 'Edges - Curved';
        } else {
            smoothElement.textContent = 'Edges - Straight';
        }
    });

    // Physics listener
    const physicsElement = document.getElementById('physics');
    physicsElement.addEventListener('click', function() {
        graph.options.physics = !graph.options.physics;
        graph.setOptions(graph.options);
        if (graph.options.physics) {
            physicsElement.textContent = "Physics - On";
        } else {
            physicsElement.textContent = "Physics - Off";
        }
    });

    // Directed/undirected graph listener
    const directedElement = document.getElementById("directed");
    directedElement.addEventListener('click', function () {
        // Update graph if directed or not directed
        directed = !directed;
        const options = {
            edges: {
                arrows: {
                    to: {
                        enabled: directed, type: 'arrow'
                    }
                },
                smooth: useSmoothEdges
            }
        };
        graph.setOptions(options);

        // Update directed button
        if (directed) {
            directedElement.textContent = "Graph - Directed";
        } else {
            directedElement.textContent = "Graph - Undirected";
        }
    });

    // Add click on graph listener;
    addListenersToClickOnGraph();

    // Add run code listeners
    addRunCodeListeners();

    // Add save listener
    addSaveListener();

    // Load project data from file (dynamically)
    loadProject();
}

// Add listeners to click events on graph
function addListenersToClickOnGraph() {
    graph.on("click", function (params) {
       if (mode === "Add") {
           // In ADD mode
           if (params.nodes.length > 0) {
               // Clicked on a node
               const nodeClicked = params.nodes[0];
               if (lastNodeClicked === null) {
                   // Track the first node to form an edge
                   lastNodeClicked = nodeClicked;
               } else {
                   // Add the two nodes to form the edge
                   edges.add({
                       from: lastNodeClicked,
                       to: nodeClicked
                   });
                   lastNodeClicked = null;
                   graph.unselectAll();
               }
           } else if (params.edges.length === 0) {
               // Did not click on an edge or a node, so create a new node
               const position = params.pointer.canvas;
               const newNodeID = getNewValidNodeID();
               nodes.add({
                   id: newNodeID,
                   label: newNodeID + "",
                   x: position.x,
                   y: position.y
               });
               lastNodeClicked = null;
               graph.unselectAll();
           }
       } else if (mode === "Remove") {
           // In REMOVE mode
           if (params.nodes.length > 0) {
               // Remove nodes
               const nodeID = params.nodes[0];
               nodes.remove(nodeID);

               // Remove any edges that did connect to this node
               edges.forEach(edge => {
                   if (edge.from === nodeID)  {
                       edges.remove(edge.id);
                   } else if (edge.to === nodeID) {
                       edges.remove(edge.id);
                   }
               });
           } else if (params.edges.length > 0) {
               // Remove edges
               const edgeID = params.edges[0];
               edges.remove(edgeID);
           }
       }
    });

    // Add listener to when a node or edge is selected on the graph
    // It should update the node or edge's value in the textbox
    graph.on("select", function (params) {
       if (mode === null) {
           const inputElement = document.getElementById("graphInput");
           if (params.nodes.length > 0) {
               // Selected node -> Update textbox with node data
               const nodeID = params.nodes[0];
               inputElement.disabled = false;
               inputElement.placeholder = "Node '" + nodeID + "' selected. Click here to update node ID.";
               selectedNode = nodeID;
               selectedEdge = null;
           } else if (params.edges.length > 0) {
               // Selected edge -> Update textbox with edge data
               const edgeID = params.edges[0];
               const edge = edges.get(edgeID);
               inputElement.disabled = false;
               inputElement.placeholder = "Edge from " + edge.from + " to " + edge.to + " selected. Click here to update edge ID.";
               selectedEdge = edgeID;
               selectedNode = null;
           }
       }
    });

    // Add listener to listen when a node or edge was deselected to clear selection
    graph.on("deselect", function (params) {
        if (mode === null) {
            if (params.previousSelection.nodes.length > 0) {
                disableGraphInput();
                selectedNode = null;
            } else if (params.previousSelection.edges.length > 0) {
                selectedEdge = null;
            }
        } else {
            disableGraphInput();
        }
    });

    // When the graphInput textbox gets updated with text and the user leaves, take this as a submission of the textbox.
    const inputElement = document.getElementById('graphInput');
    inputElement.addEventListener("blur", function (event) {
        if (mode === null) {
            const value = inputElement.value;
            if (selectedNode != null) {
                // Selected a node and trying to update the node ID
                const node = nodes.get(selectedNode);
                if (node !== undefined && value !== "") {
                    if (isNodeIDValid(value)) {
                        // Valid node id, update the node
                        updateNodeID(node, selectedNode, value);
                        inputElement.placeholder = "Updated node ID to " + value + ".";
                    } else {
                        // Invalid node id, do not update the node
                        inputElement.placeholder = "That node ID is invalid.";
                    }
                }
            } else if (selectedEdge != null) {
                // Selected an edge and trying to update its weight
                const edge = edges.get(selectedEdge);
                if (edge !== undefined) {
                    if (value === "") {
                        // Clear the weight of the edge
                        edges.update({
                           id: edge.id,
                           label: " "
                        });
                        inputElement.placeholder = "Removed weight from edge.";
                    } else {
                        // Set the weight of the edge
                        edges.update({
                           id: edge.id,
                           label: value
                        });
                        inputElement.placeholder = "Set weight for edge.";
                    }
                }
            }
            inputElement.value = "";
        }
    });
}

function addSaveListener() {
    // Allow the save button to update its hidden input to save the code to the database on
    document.getElementById("submitForm").addEventListener("submit", function(event) {
        event.preventDefault();
        saveProject();
    });
}

function loadProject() {
    const graphDataElement = document.getElementById("graphDataHidden");
    const graphDataValue = JSON.parse(graphDataElement.textContent);

    // Update noes and edges
    nodes.clear()
    edges.clear()
    nodes.update(graphDataValue.nodes);
    edges.update(graphDataValue.edges);

    // Update options
    useSmoothEdges = graphDataValue.options.smooth;
    directed = graphDataValue.options.arrows.to.enabled;
    const options = {
        physics: !!(graphDataValue.options.physics),
        edges: {
            arrows: {
                to: {
                    enabled: directed, type: 'arrow'
                }
            },
            smooth: useSmoothEdges
        }
    };
    graph.setOptions(options);

    // Update displays of physics, directed, and edge smoothed buttons
    const physicsButton = document.getElementById('physics');
    if (graphDataValue.options.physics) {
        physicsButton.textContent = "Physics - On";
    }
    const directedButton = document.getElementById('directed');
    if (directed) {
        directedButton.textContent = "Graph - Directed";
    }
    const smoothEdgeButton = document.getElementById('smoothEdge');
    if (useSmoothEdges) {
        smoothEdgeButton.textContent = "Edges - Curved"
    }
}

function saveProject() {
    console.log("Saving project");
    const url = "index.php?command=saveProjectGraphAndCode";
    const request = new XMLHttpRequest();
    request.open('POST', url, true);

    const statusTextElement = document.getElementById('statusText');
    statusTextElement.textContent = "Saving...";
    statusTextElement.classList.add("text-danger");
    statusTextElement.classList.remove("d-none");
    statusTextElement.classList.remove("text-secondary");

    request.onload = () => {
      if (request.status === 200) {
          statusTextElement.textContent = "Saved.";
          statusTextElement.classList.remove("text-danger");
          statusTextElement.classList.add("text-secondary");
          console.log("Saved.");
      }
    };

    request.onerror = () => {
        console.log("Error saving project!");
    }

    const formData = new FormData();
    const graphData = {
        nodes: nodes.get(),
        edges: edges.get(),
        options: {
            physics: !!(graph.options.physics),
            smooth: useSmoothEdges,
            arrows: {
                to: {
                    enabled: directed, type: 'arrow'
                }
            }
        }
    };
    formData.append('graph_data', JSON.stringify(graphData));
    formData.append("project_id", document.getElementById("project_id").value);
    formData.append("graph_code", editor.getValue());
    request.send(formData);
}

function addRunCodeListeners() {
    // Add listener to run the code and dynamically update the runButton
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const stopButton = document.getElementById("stopButton");
    const statusText = document.getElementById("statusText");
    runButton.addEventListener("click", function (event) {
        if (runMode === "Stopped") {
            // Stopped -> Running mode
            run();
        } else if (runMode === "Running") {
            // Running -> Paused mode
            stop();
        }
    });

    document.getElementById('errorButton').addEventListener('click', function () {
        const errorBannerElement = document.getElementById('errorBanner');
        errorBannerElement.classList.add('d-none');
    });
}

// Gets a new valid node (that is numeric and not used)
function getNewValidNodeID() {
    let currNode = nodes.length;
    while (!isNodeIDValid(currNode)) {
        currNode += 1;
    }
    return currNode;
}

// Determines if a node ID is valid (not used or empty)
function isNodeIDValid(nodeID) {
    if (nodeID === "") return false;
    let isValid = true;
    nodes.forEach(node => {
        if (node.id === nodeID) {
            isValid = false;
        }
    })
    return isValid;
}

// Updates a node's ID (oldNode = nodeData, oldNodeID = nodeData.ID, newID = new node ID)
function updateNodeID(oldNode, oldNodeID, newID) {
    // Add new node
    const newNode = {
        ... oldNode,
        id: newID,
        label: newID
    };
    nodes.add(newNode);

    // Update edges that previously referenced the older node
    edges.forEach(edge => {
       let didUpdate = false
       if (edge.from === oldNodeID)  {
           edge.from = newID;
           didUpdate = true;
       }
       if (edge.to === oldNodeID) {
           edge.to = newID;
           didUpdate = true;
       }
       if (didUpdate) {
           edges.update(edge);
       }
    });

    // Remove old node
    nodes.remove(oldNode);
}

// Update graph buttons on the DOM
function updateGraphButtons() {
    // Update Add Node
    const addNode = document.getElementById('add');
    if (mode === "Add") {
        addNode.textContent = "Adding";
        addNode.classList.add("graphButtonActive");
        graph.unselectAll();
        disableGraphInput();
    } else {
        addNode.textContent = "Add";
        addNode.classList.remove("graphButtonActive");
    }

    // Update remove node
    const removeNode = document.getElementById("remove");
    if (mode === "Remove") {
        removeNode.textContent = "Removing";
        removeNode.classList.add("graphButtonActive");
        graph.unselectAll();
        disableGraphInput();
    } else {
        removeNode.textContent = "Remove";
        removeNode.classList.remove("graphButtonActive");
    }

    lastNodeClicked = null;
}

// Disable graph input box
function disableGraphInput() {
    const inputElement = document.getElementById("graphInput");
    inputElement.disabled = true;
    inputElement.placeholder = "";
    inputElement.value = "";
}

function showErrorAlert(errorText) {
    const errorElement = document.getElementById('errorText');
    const errorBannerElement = document.getElementById('errorBanner');
    errorBannerElement.classList.remove('d-none');
    errorElement.textContent = errorText;
}

async function run() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const statusText = document.getElementById("statusText");

    console.clear();
    runMode = "Running";

    // Run new worker thread
    const editor = ace.edit("editor");
    const code = editor.getValue();
    worker = new CodeWorker();
    worker.start(code);

    // Update running icon
    runIcon.classList.add('bi-stop-fill');
    runIcon.classList.remove('bi-play-fill');
    runButton.classList.add('btn-danger');
    runButton.classList.remove('btn-outline-dark');
    runButton.classList.remove('btn-light');

    // Update status text
    statusText.classList.remove('d-none');
    statusText.classList.add('text-danger');
    statusText.classList.remove('text-secondary');
    statusText.textContent = "Running...";
}

function stop() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const statusText = document.getElementById("statusText");

    runMode = "Stopped";
    worker.terminate();

    // Update running icon
    runIcon.classList.remove('bi-stop-fill');
    runIcon.classList.add('bi-play-fill');
    runButton.classList.add('btn-outline-dark');
    runButton.classList.remove('btn-danger');
    runButton.classList.remove('btn-secondary');

    // Update status text
    statusText.classList.remove('text-danger');
    statusText.classList.add('text-secondary');
    statusText.textContent = 'Finished.';
}
