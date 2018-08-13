const path = require("path");
const ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
  entry: ["./js/kirkanta.js", "./scss/kirkanta.scss"],
  output: {
    path: path.resolve(__dirname, "dist"),
    filename: "kirkanta.js"
  },
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: ExtractTextPlugin.extract({
          use: [
            {loader: "css-loader"},
            {
              loader: "postcss-loader",
              options: {
                plugins: function() {
                  return [
                    require("precss"),
                    require("autoprefixer")
                  ]
                }
              }
            },
            {
              loader: "sass-loader"
            }
          ]
        })
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin({filename: "kirkanta.css"}),
  ]
};
