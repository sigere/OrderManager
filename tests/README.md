## Testing
- All testes are based on data from data fixtures located in `src/DataFixtures`.
- Each test is wrapped in a transaction that is rolled back after the test is finished thanks to the <a href="https://github.com/dmaicher/doctrine-test-bundle">dama/doctrine-test-bundle</a>.