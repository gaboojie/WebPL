const nodes = new vis.DataSet([]);
const edges = new vis.DataSet([]);

// Randomly add nodes across the graph
for (let i = 0; i < 50; i++) {
    nodes.add(
        {
            id: i,
            color: {
                background: 'rgb(207, 168, 115)',
                border: 'rgb(207, 168, 115)'
            }
        }
    )
}

// Randomly add edges
for (let i = 0; i < nodes.length; i++) {
    edges.add({
       from: i,
       // Randomly generate where the node is going
       to: Math.floor(Math.random() * nodes.length)
    });
}


// Add hidden node (that is bound to the cursor to create movement in the graph)
hiddenNode = {
    id: nodes.length,
    x: window.innerWidth / 2,
    y: window.innerHeight / 2,
    size: 80,
    color: {
        background: 'transparent',
        border: 'transparent'
    }
};
nodes.add(hiddenNode);


// Create network graph
const graphContainer = document.getElementById('bg-graph');
const data = { nodes, edges };
const options = {
    interaction: {
        dragNodes: true,
        dragView: false,
        zoomView: false
    },
    physics: true,
    edges: {
        smooth: false
    },
    nodes: {
        shape: "dot",
        size: 40
    }
}
const graph = new vis.Network(graphContainer, data, options);


// When the mouse moves, modify the hiddenNode to be bound to the cursor's position
const body = document.getElementById('body');
body.addEventListener("mousemove", (event) => {
    const rect = graphContainer.getBoundingClientRect();

    const DOMpos = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
    };
    const canvasPos = graph.DOMtoCanvas(DOMpos);
    nodes.update({
       id:  hiddenNode.id,
       x: canvasPos.x,
       y: canvasPos.y
    });
});