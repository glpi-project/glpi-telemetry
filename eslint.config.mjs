
import globals from "globals";
import js from "@eslint/js";
import stylistic from '@stylistic/eslint-plugin';

export default [
    {
        ignores: [
            "public/build/*",
            "vendor/*",
        ],
    },
    js.configs.recommended,
    {
        plugins: {
            js,
            '@stylistic': stylistic,
        },
        rules: {
            "no-console": [
                "error",
                {
                    "allow": ["warn", "error"],
                }
            ],
            "@stylistic/eol-last": ["error", "always"],
            "@stylistic/indent": [
                "error",
                4,
                {
                    "SwitchCase": 1,
                }
            ],
            "@stylistic/linebreak-style": ["error", "unix"],
            "@stylistic/semi": ["error", "always"],
        },
    },
    {
        files: ["eslint.config.js", "webpack.config.js"],
        languageOptions: {
            globals: {
                ...globals.node,
            }
        },
    },
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.commonjs,
            },
            sourceType: "module"
        }
    }
];
