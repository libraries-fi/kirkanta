const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const autoprefixer = require("autoprefixer");
const path = require("path");
const webpack = require("webpack");

module.exports = {
  mode: "development",
  entry: {
    script: ["./public/js/init.webpack.js"],
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
          "css-loader",
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
      },
      {
        test: /\.(png|jpg|gif|eot|woff|ttf|svg)$/,
        use: [
          {
            loader: "url-loader",
            options: {
              limit: 200,
            }
          }
        ]
      }
    ],
  },
  plugins: [
    new MiniCssExtractPlugin,
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      moment: "moment",
    })
  ]
};
