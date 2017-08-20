module.exports = {
  apps: [
    {
      name: "site",
      script: "npm",
      args: "start",
      watch: true,
      ignore_watch: ["node_modules", "wp-content", "wp-admin", "wp-includes"],
      watch_options: {
        followSymlinks: false
      },
      env: {
        NODE_ENV: "production"
      }
    }
  ]
};
