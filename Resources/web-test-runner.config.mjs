export default {
  nodeResolve: true,
  // Our QUnit tests expect the translator to be loaded globally so we need to inject it in the runner HTML
  testRunnerHtml: testFramework =>
    `
    <!DOCTYPE html>
    <html lang="fr">
      <head>
          <meta charset="utf-8">
          <title>JSTranslationBundle Unit Tests</title>
          <script src="node_modules/intl-messageformat/intl-messageformat.iife.js"></script>
          <script src="js/translator.js"></script>
      </head>
      <body>
        <script type="module" src="${testFramework}"></script>
      </body>
    </html>
    `,
  testFramework: {
    path: './node_modules/web-test-runner-qunit/dist/autorun.js',
    config: {}
  }
}
