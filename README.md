# AHOY!

Ahoy! is a CodeIgniter library for managing you sites navigational menus. Add menu items and generate a list of links.

## Features:

- Save you a load of boring foreach loops or manually coding generic menus
- Auto detect active menu items and add active class
- Intergration with [Bootstrap, from Twitter](http://twitter.github.com/bootstrap/index.html)

## Installation

### Method One

Load the ahoy library from inside your codeigniter controller

	$this->load->library('ahoy');

### Method Two

If you are going to need ahoy on every page in your site, load it in your config/autoload.php file

	$autoload['libraries'] = array('ahoy');

That's it! Ahoy uses some of built in CodeIgniter helper files, but it will auto load them it's self if required.