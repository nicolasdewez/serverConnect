# Server Connect

## Installation

Use composer for install this application.

```sh
composer install
```

## Configuration

### Create a configuration file

Configuration file must be created into directory `config`. 
For example : `test.yml`

### Complete this file same this example
 
 ```yaml
config:
    connections:
        connection_1:
            host: myhost
            username: myuser
            password: mypassword
        connection_2:
            host: myhost
            username: myuser2
            password: mypassword2

 ```
 
### Build shell

Execute this command:

```sh
app.php build test
```

The parameter `test` is the configuration name

After execution, a script was created into directory build (name is `test.sh`)
