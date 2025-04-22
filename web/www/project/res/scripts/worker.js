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
            `function ${this.workerLogic.toString()}
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
                stop();
            }
        };
    }

    // This function defines the worker logic
    workerLogic() {
        // When the worker receives a message, this function will handle it
        self.onmessage = async function (e ) {
            const { command, data } = e.data;
            if (command === 'start') {
                // Handle start command and execute the code passed in data.code

                // Include step function in code (for displaying which line is executing and allowing for periodic awaits)
                const stepFn =
                    `async function step(line) {
                          await new Promise(resolve => setTimeout(resolve, 100));
                          while (self.paused) {
                                await new Promise(resolve => setTimeout(resolve, 100));
                          }
                    }\n`;
                const combinedCode = `${stepFn}\n${data.code}`;

                // Set state
                self.paused = false;
                self.terminated = false;
                console.log("Running code...");

                // Include code to run
                const blob = new Blob([combinedCode], {
                    type: "text/javascript"
                });
                const url = URL.createObjectURL(blob);
                try {
                    await import(url);
                } catch (error) {
                    console.error("Execution error:", error);
                }

                // Tell the main thread that the worker finished (so the main thread knows to update the DOM/state)
                self.terminated = true;
                console.log("Code finished.");
                postMessage({
                    command: 'finished',
                    data: {}
                });
                self.close();
            } else if (command === 'pause' && !self.paused) {
                // Handle pause state
                self.paused = true;
                console.log("Paused code.");
            } else if (command === 'resume' && self.paused) {
                // Handle resume state
                self.paused = false;
                console.log("Resumed code");
            } else if (command === 'terminate' && !terminated) {
                // Handle terminate state
                self.terminated = true;
                console.log("Terminated code.");
                self.close();
            }
        }
    }

    // A function that will tell the worker to start with code as the input code
    start(code) {
        // Transform the code using Babel to insert step() and async functions
        const transformed = Babel.transform(code, {
            plugins: [insertStepPlugin],
            parserOpts: { sourceType: "script" }
        }).code;

        // Start the worker with the transformed code
        this.worker.postMessage({
            command: 'start',
            data: {
                code: transformed
            }
        });
    }

    // A function that will tell the worker to pause from the main thread
    pause() {
        this.worker.postMessage({ command: 'pause', data: {} });
    }

    // A function that will tell the worker to resume from the main thread
    resume() {
        this.worker.postMessage({ command: 'resume', data: {} });
    }

    // A function that will tell the worker to terminate from the main thread
    terminate() {
        this.worker.postMessage({ command: 'terminate', data: {} });
        this.worker.terminate();
    }
}