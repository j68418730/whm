#!/usr/bin/env node

const path = require('path');

// Ensure we run from the connector directory
process.chdir(path.dirname(__dirname));

require('../index.js');