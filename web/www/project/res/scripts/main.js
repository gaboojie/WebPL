// Instantiate global 'graph' variable
let graph = null;
let mode = null;
let lastNodeClicked = null;
let selectedEdge = null;
let selectedNode = null;
let directed = false;

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
    // Get data
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
    document.getElementById('add').addEventListener('click', function() {
        // Toggle mode
        mode = (mode === "Add") ? null : "Add";
        updateGraphButtons();
    });

    // Remove node listener
    document.getElementById('remove').addEventListener('click', function() {
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
        if (directed) {
            directedElement.textContent = "Switch to an undirected graph.";
        } else {
            directedElement.textContent = "Switch to a directed graph.";
        }
    });

    // Add click on graph listener;
    addListenerToClickOnGraph();
}

// Add listen to click events on graph
function addListenerToClickOnGraph() {
    graph.on("click", function (params) {
       if (mode === "Add") {
           if (params.nodes.length > 0) {
               const nodeClicked = params.nodes[0];
               if (lastNodeClicked === null) {
                   lastNodeClicked = nodeClicked;
               } else {
                   edges.add({
                       from: lastNodeClicked,
                       to: nodeClicked
                   });
                   lastNodeClicked = null;
                   graph.unselectAll();
               }
           } else {
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
           if (params.nodes.length > 0) {
               const nodeID = params.nodes[0];
               nodes.remove(nodeID);
           } else if (params.edges.length > 0) {
               const edgeID = params.edges[0];
               edges.remove(edgeID);
           }
       }
    });

    graph.on("select", function (params) {
       if (mode === null) {
           if (params.nodes.length > 0) {
               const nodeID = params.nodes[0];
               const inputElement = document.getElementById("graphInput");
               inputElement.disabled = false;
               inputElement.placeholder = "Node '" + nodeID + "' selected. Click here to update node ID.";
               selectedNode = nodeID;
               selectedEdge = null;
           } else if (params.edges.length > 0) {
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

    const inputElement = document.getElementById('graphInput');
    inputElement.addEventListener("blur", function (event) {
        if (mode === null) {
            const value = inputElement.value;
            if (selectedNode != null) {
                const node = nodes.get(selectedNode);
                if (node !== undefined && value !== "") {
                    if (isNodeIDValid(value)) {
                        updateNodeID(node, selectedNode, value);
                        inputElement.placeholder = "Updated node ID to " + value + ".";
                    } else {
                        inputElement.placeholder = "That node ID is invalid.";
                    }
                }
            } else if (selectedEdge != null) {
                const edge = edges.get(selectedEdge);
                if (edge !== undefined) {
                    if (value === "") {
                        edges.update({
                           id: edge.id,
                           label: " "
                        });
                        inputElement.placeholder = "Removed weight from edge.";
                    } else {
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

function getNewValidNodeID() {
    let currNode = nodes.length;
    while (!isNodeIDValid(currNode)) {
        currNode += 1;
    }
    return currNode;
}

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

function updateNodeID(oldNode, oldNodeID, newID) {
    // Add new node
    const newNode = {
        ... oldNode,
        id: newID,
        label: newID
    };
    nodes.add(newNode);

    // Update edges
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