# Apache Housekeeper Tool

A tool that provides a cron job to disable apache sites when they've not been accessed for a while

### Installation on mac/linux
This will install the phar to the current directory
```
curl -s https://raw.githubusercontent.com/garethmidwood/apache-housekeeper/master/install.sh | bash -s
```

## Example Usage
Run the installation command in a non-web-accessible directory of your choice.

The install will download a `ahousekeeper.yml.example` file containing an example configuration. You should copy this to `ahousekeeper.yml` and configure for this server.

### Example configuration file

```
path: /etc/apache2/sites-enabled
cutoff: 30
```

### What are the config settings?
<dl>
  <dt>path</dt>
  <dd>The path to the `sites-enabled` directory for apache</dd>

  <dt>cutoff</dt>
  <dd>The number of visitor-free days before a site is disabled - defaults to 30</dd>
</dl>

