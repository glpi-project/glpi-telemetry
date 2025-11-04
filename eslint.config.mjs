
import { defineConfig, globalIgnores } from "eslint/config";
import globals from "globals";
import js from "@eslint/js";
import stylistic from '@stylistic/eslint-plugin';

export default defineConfig(
    [
        globalIgnores(["public/build/*", "vendor/*"]),
        {        
            languageOptions: {
                globals: {
                    ...globals.browser,
                    ...globals.commonjs,
                },
    
                "sourceType": "module",
                parserOptions: {},
            },
            plugins: {
                js,
                '@stylistic': stylistic,
            },
            extends: ["js/recommended"],
            rules: {
                "no-console": [
                    "error",
                    {
                        "allow": ["warn", "error"],
                    }
                ],
                "@stylistic/eol-last": ["error", "always"],

                "@stylistic/indent": ["error", 4, {
                    "SwitchCase": 1,
                }],

                "@stylistic/linebreak-style": ["error", "unix"],
                "@stylistic/semi": ["error", "always"],
            },
        },
        {
            files: ["eslint.config.js", "webpack.config.js"],

            languageOptions: {
                globals: {
                    ...globals.node,
                },
            },
        }
    ]
);
