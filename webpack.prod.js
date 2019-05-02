const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const merge = require("webpack-merge");
const config = require("./webpack.config.js");
const path = require("path");

module.exports = merge(config, {
  mode: "production",
  output: {
    path: path.resolve(__dirname, "public/dist")
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            cacheDirectory: true,
            presets: [
              ["@babel/preset-env", {
                modules: false,
                targets: {
                  ie: "11"
                }
              }]
            ],
            plugins: ["@babel/plugin-transform-runtime"]
          }
        }
      }
    ]
  }
});
