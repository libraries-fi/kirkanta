const path = require("path");
const autoprefixer = require("autoprefixer");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const webpack = require("webpack");

module.exports = {
  mode: "development",
  entry: {
    script: ["./public/js/kirkanta.js"],
    style: ["./public/scss/kirkanta.scss"],
  },
  output: {
    path: path.resolve(__dirname, "public/dist"),
    filename: "[name].js"
  },
  module: {
    rules: [
      {
        test: /\.scss/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              url: false,
            }
          },
          {
            loader: "postcss-loader",
            options: {
              plugins: [autoprefixer]
            }
          },
          "sass-loader"
        ]
      },
      {
        test: /\.css$/,
        loader: "style-loader!css-loader",
      }
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({filename: "[name].css"}),
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      moment: "moment",
    })
  ]
};
