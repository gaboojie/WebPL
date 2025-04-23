/*
   This code is quite complicated as it deals with sending messages to a thread that executes the user's code
   It uses JS' Worker() class to spawn a thread that can be ran, paused, resumed, and terminated.

   Sources:
   https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Using_web_workers
 */

class CodeWorker {

    constructor() {
        // Define workerLogic that the worker uses
        const workerCode =
            `
            function ${this.workerLogic.toString()}
            this.workerLogic();
        `;

        // Create executable code that the worker can run
        const blob = new Blob([workerCode], { type: "application/javascript" });
        this.worker = new Worker(URL.createObjectURL(blob));

        // Handle messages that come from the worker
        this.worker.onmessage = (e) => {
            const { command, data } = e.data;

            // If worker finished, send signal to main thread to stop()
            if (command === 'finished') {
                if ('error' in data) {
                    showErrorAlert(data.error);
                }
                stop();
            } else if (command === 'getNodes') {
                // Send nodes data to Worker
                this.worker.postMessage({
                   command: 'getNodes',
                   data: {
                       nodes: nodes.get()
                   }
                });
            } else if (command === 'getEdges') {
                // Send edges data to Worker
                this.worker.postMessage({
                    command: 'getEdges',
                    data: {
                        edges: edges.get()
                    }
                });
            } else if (command === 'setNodes') {
                nodes.clear();
                nodes.update(data.nodes);
            } else if (command === 'setEdges') {
                edges.clear();
                edges.update(data.edges);
            }
        };
    }

    // This function defines the worker logic
    workerLogic() {
        globalThis.nodesCache = undefined;
        globalThis.edgesCache = undefined;
        // When the worker receives a message, this function will handle it
        self.onmessage = async function (e ) {
            const { command, data } = e.data;
            if (command === 'start') {
                // Handle start command and execute the code passed in data.code

                // Set state
                self.terminated = false;
                console.log("Running code...");

                const workerCodeToExecute = `
                    function finished() {
                        postMessage({
                            command: 'finished',
                            data: {}
                        });
                    }
                    async function getNodes() {
                        globalThis.nodesCache = undefined;
                        postMessage({
                            command: 'getNodes'
                        });
                        while (globalThis.nodesCache === undefined) {
                            await new Promise(resolve => setTimeout(resolve, 10));
                        }
                        return globalThis.nodesCache;
                    }
                    async function getEdges() {
                        globalThis.edgesCache = undefined;
                        postMessage({
                            command: 'getEdges'
                        });
                        while (globalThis.edgesCache === undefined) {
                            await new Promise(resolve => setTimeout(resolve, 10));
                        }
                        return globalThis.edgesCache;                    
                    }
                    function setNodes(nodes) {
                        postMessage({
                            command: 'setNodes',
                            data: {
                                nodes: nodes
                            }
                        });
                    }
                    function setEdges(edges) {
                        postMessage({
                            command: 'setEdges',
                            data: {
                                edges: edges
                            }
                        });
                    }
                    ${data.code}
                `;

                // Include code to run
                const blob = new Blob([workerCodeToExecute], {
                    type: "text/javascript"
                });
                const url = URL.createObjectURL(blob);
                try {
                    await import(url);
                } catch (error) {
                    console.error("Execution error:", error);
                    postMessage({
                        command: 'finished',
                        data: {
                            error: 'Error: ' + error.name + ' - ' + error.message
                        }
                    });
                }
            }  else if (command === 'terminate' && !terminated) {
                // Handle terminate state
                self.terminated = true;
                self.close();
            } else if (command === 'getNodes') {
                globalThis.nodesCache = data.nodes;
            } else if (command === 'getEdges') {
                globalThis.edgesCache = data.edges;
            }
        }
    }

    // A function that will tell the worker to start with code as the input code
    start(code) {
        // Start the worker with the transformed code
        this.worker.postMessage({
            command: 'start',
            data: {
                code: code
            }
        });
    }

    // A function that will tell the worker to terminate from the main thread
    terminate() {
        console.log("Terminated code.");
        self.terminated = true;
        this.worker.postMessage({ command: 'terminate', data: {} });
        this.worker.terminate();
    }
}