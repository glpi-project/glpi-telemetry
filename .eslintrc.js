module.exports = {
    "root": true,
    "ignorePatterns": [
        "/node_modules/*",
        "/public/build/*",
        "/vendor/*",
    ],
    "env": {
        "browser": true,
        "commonjs": true,
        "es2017": true,
    },
    "parserOptions": {
        "sourceType": "module",
    },
    "extends": "eslint:recommended",
    "plugins": [
        "@stylistic/js",
    ],
    "rules": {
        "no-console": ["error", {"allow": ["warn", "error"]}],
        "@stylistic/js/eol-last": ["error", "always"],
        "@stylistic/js/indent": ["error", 4, {"SwitchCase": 1}],
        "@stylistic/js/linebreak-style": ["error", "unix"],
        "@stylistic/js/semi": ["error", "always"],
    },
    "overrides": [
        {
            "files": [".eslintrc.js", "webpack.config.js"],
            "env": {
                "node": true
            }
        },
    ],
};
