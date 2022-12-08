const path = require('path');

const public = {
    mode: "production",
    watch: true,

    entry: path.resolve(__dirname, 'assets/scripts/src/public.js'),

    output: {
        filename: 'public.js',
        path: path.resolve(__dirname, 'assets/scripts/dist/'),
    },
};

const admin = {
    mode: "production",
    watch: true,

    entry: path.resolve(__dirname, 'assets/scripts/src/admin.js'),

    output: {
        filename: 'admin.js',
        path: path.resolve(__dirname, 'assets/scripts/dist/'),
    },
};

module.exports = [public, admin];
