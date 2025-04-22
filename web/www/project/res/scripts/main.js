// Instantiate global variables
let graph = null;
let mode = null;
let lastNodeClicked = null;
let selectedEdge = null;
let selectedNode = null;
let directed = false;
let runMode = "Stopped";
let worker = null;

// Define template nodes and edges
let nodes = [
    { id: "0", label: "0", x: 0, y: 0 },
    { id: "1", label: "1", x: 50, y: 0 },
    { id: "2", label: "2", x: 50, y: 50 },
    { id: "3", label: "3", x: 0, y: 50 },
];
let edges = [
    {from: "0", to: "1"},
    {from: "1", to: "2"},
    {from: "2", to: "3"},
    {from: "3", to: "0"},
];

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
            }
        }
    };

    // Create graph
    var container = document.getElementById("graph");
    graph = new vis.Network(container, data, options);

    // Add listeners
    addGraphButtonListeners();

    // Initially disable graph input
    disableGraphInput();
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

    // Physics listener
    const physicsElement = document.getElementById('physics');
    physicsElement.addEventListener('click', function() {
        graph.options.physics = !graph.options.physics;
        graph.setOptions(graph.options);
        if (graph.options.physics) {
            physicsElement.textContent = "Turn physics off.";
        } else {
            physicsElement.textContent = "Turn physics on.";
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
                }
            }
        };
        graph.setOptions(options);

        // Update directed button
        if (directed) {
            directedElement.textContent = "Switch to an undirected graph.";
        } else {
            directedElement.textContent = "Switch to a directed graph.";
        }
    });

    // Add click on graph listener;
    addListenersToClickOnGraph();

    // Add run code listeners
    addRunCodeListeners();
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
           if (params.nodes.length > 0) {
               // Selected node -> Update textbox with node data
               const nodeID = params.nodes[0];
               const inputElement = document.getElementById("graphInput");
               inputElement.disabled = false;
               inputElement.placeholder = "Node '" + nodeID + "' selected. Click here to update node ID.";
               selectedNode = nodeID;
               selectedEdge = null;
           } else if (params.edges.length > 0) {
               // Selected edge -> Update textbox with edge data
               const edgeID = params.edges[0];
               const edge = edges.get(edgeID);
               const inputElement = document.getElementById("graphInput");
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
            pause();
        } else if (runMode === "Paused") {
            resume();
        }
    });

    // Add listener to stopButton
    stopButton.addEventListener("click", function (event) {
        stop();
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
        addNode.classList.add("active");
    } else {
        addNode.textContent = "Add";
        addNode.classList.remove("active");
    }

    // Update remove node
    const removeNode = document.getElementById("remove");
    if (mode === "Remove") {
        removeNode.textContent = "Removing";
        removeNode.classList.add("active");
    } else {
        removeNode.textContent = "Remove";
        removeNode.classList.remove("active");
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

async function run() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const stopButton = document.getElementById("stopButton");
    const statusText = document.getElementById("statusText");

    console.clear();
    runMode = "Running";

    // Run new worker thread
    const editor = ace.edit("editor");
    const code = editor.getValue();
    worker = new CodeWorker();
    worker.start(code);

    // Update running icon
    runIcon.classList.add('bi-pause-fill');
    runIcon.classList.remove('bi-play-fill');
    runButton.classList.add('btn-danger');
    runButton.classList.remove('btn-dark');

    // Update status text
    statusText.classList.remove('d-none');
    statusText.textContent = "Running...";

    // Update stop
    stopButton.classList.remove('d-none');
    stopButton.classList.add('btn-danger');
    stopButton.classList.remove('btn-secondary');
}

function pause() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const stopButton = document.getElementById("stopButton");
    const statusText = document.getElementById("statusText");

    runMode = "Paused";
    worker.pause();

    // Update running icon
    runIcon.classList.remove('bi-pause-fill');
    runIcon.classList.add('bi-play-fill');
    runButton.classList.remove('btn-danger');
    runButton.classList.add('btn-secondary');

    // Update status text
    statusText.textContent = "Paused.";
    statusText.classList.add("text-secondary");
    statusText.classList.remove("text-danger");

    // Update stop
    stopButton.classList.remove('d-none');
    stopButton.classList.add('btn-secondary');
    stopButton.classList.remove('btn-danger');
}

function resume() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const stopButton = document.getElementById("stopButton");
    const statusText = document.getElementById("statusText");

    runMode = "Running";
    worker.resume();

    // Update running icon
    runIcon.classList.add('bi-pause-fill');
    runIcon.classList.remove('bi-play-fill');
    runButton.classList.add('btn-danger');
    runButton.classList.remove('btn-secondary');

    // Update status text
    statusText.textContent = "Running...";
    statusText.classList.remove("text-secondary");
    statusText.classList.add("text-danger");

    // Update stop
    stopButton.classList.remove('d-none');
    stopButton.classList.remove('btn-secondary');
    stopButton.classList.add('btn-danger');
}

function stop() {
    const runButton = document.getElementById("runButton");
    const runIcon = document.getElementById("runIcon");
    const stopButton = document.getElementById("stopButton");
    const statusText = document.getElementById("statusText");

    runMode = "Stopped";
    worker.terminate();

    // Update running icon
    runIcon.classList.remove('bi-pause-fill');
    runIcon.classList.add('bi-play-fill');
    runButton.classList.add('btn-dark');
    runButton.classList.remove('btn-danger');
    runButton.classList.remove('btn-secondary');

    // Update status text
    statusText.classList.add("d-none");

    // Update stop
    stopButton.classList.add('d-none');
}
