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
enabled-path: /etc/apache2/sites-enabled
available-path: /etc/apache2/sites-available
config-extension: conf # this is default
cutoff: 30
```

### What are the config settings?
<dl>
  <dt>enabled-path</dt>
  <dd>The path to the `sites-enabled` directory for apache - <strong>default: /etc/apache2/sites-enabled</strong></dd>

  <dt>available-path</dt>
  <dd>The path to the `sites-available` directory for apache - <strong>default: /etc/apache2/sites-available</strong></dd>

  <dt>config-extension</dt>
  <dd>The vhost config extension - <strong>default: conf</strong></dd>

  <dt>cutoff</dt>
  <dd>The number of visitor-free days before a site is disabled - defaults to 30</dd>
</dl>

### Running a scan

This will automatically disable sites that have not been accessed (based on the last write to access.log) since the configured cutoff date.
You may find that bots are accessing your sites every day, if this is the case you'll need to block them (using a firewall perhaps) from hitting apache, or add apache config to prevent bots writing access logs.

*If the apache config file does not contain an `access.log` file then this will not work.*

The command should be run as a user that has permissions to disable sites

```
php ahousekeeper.phar scan
```

