const insertStepPlugin = ({types: t}) => ({
   // Use visitor pattern to traverse AST
   visitor: {
       // For a given block statement: i.e. { ... }
       BlockStatement(path) {
           // Iterate through the body of the block
           const body = path.get("body");
           for (let i = 0; i < body.length; i++) {
               const statement = body[i];

               // If location is not availabe, ignore this statement
               if (!statement.node.loc) continue;
               const line = statement.node.loc.start.line;

               // If the current statement is not an expression and is not an await expression (to avoid inserting step() after a previously-inserted step())
               if (!statement.isExpressionStatement() || !statement.get("expression").isAwaitExpression()) {
                   // Insert a new statement (before this statement) that calls the step function
                   // It passes in the line number of the statement for syntax highlighting purposes for ACE
                   const stepCall = t.expressionStatement(
                     t.awaitExpression(
                         t.callExpression(t.identifier("step"), [
                             t.numericLiteral(line)
                         ])
                     )
                   );

                   // Insert before the statement
                   statement.insertBefore(stepCall);
               }
           }
       }
   }
});

// Step function

async function runUserCode(userCode) {
    // Transform the user's code according to the plugin
    const transformed = Babel.transform(userCode, {
        plugins: [insertStepPlugin],
        parserOpts: { sourceType: "script" }
    }).code;

    console.log(transformed);

    const stepFn = `
        async function step(line) {
            await new Promise(resolve => setTimeout(resolve, 1000));
            console.log("Executing line", line);
        }
    `;

    const code = `
        ${stepFn}
        async function toRun(val) {
            console.log(val);
        }
        
        for (let i = 0; i < 10; i++) {
            await step(2);
            toRun("Hello it is" + i);
        }
    `;

    try {
        eval(`(async () => { ${code} })()`);
    } catch (error) {
        console.error("Execution error:", error);
    }
}

const code =
`function toRun(val) {
    console.log(val);
}

for (let i = 0; i < 10; i++) {
    toRun("Hello it is", i);
}
`;

runUserCode(code);