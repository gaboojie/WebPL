const insertStepPlugin = ({types: t}) => ({
   // Use visitor pattern to traverse AST
   visitor: {
       // Force functions to be async (required for code to execute)
       Function(path) {
           if (!path.node.async) {
               path.node.async = true;
           }
       },
       // For a given block statement: i.e. { ... }
       BlockStatement(path) {
           // Iterate through the body of the block
           const body = path.get("body");
           for (let i = 0; i < body.length; i++) {
               const statement = body[i];

               // If location is not availabe, ignore this statement
               if (!statement.node.loc) continue;
               const line = statement.node.loc.start.line;

               // If the current statement is not an expression or is not an await expression (to avoid inserting step() after a previously-inserted step())
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