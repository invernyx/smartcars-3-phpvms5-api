<h1 align="center">smartCARS 3 Public Web API</h1>
<div align="center">
    <i>Web Script Files for smartCARS 3</i>
</div>

## Introduction
smartCARS 3 is a web-based flight tracking system for virtual airlines. It is a complete rewrite of the original smartCARS system, and is designed to be more flexible and easier to use. This repository contains the web script files for smartCARS 3.

## Installation
The smartCARS 3 API has two "handlers", which represent the platforms it is communicating with. We support the following platforms:
- phpVMS 5.X
- phpVMS 7.X

If you are running these platforms, you already have the required versions for the rest of the software. If you are running a different platform, you will need to write your own handler.

### Step 1
Download the latest release from the [releases page](https://github.com/invernyx/smartcars-3-public-api/releases).

### Step 2
Extract the contents of the zip file to the top level of the platform you are using (for phpVMS 5, the same level as the `core` folder, for phpVMS 7, the same level as the `bootstrap` folder). You may need to create a new folder if one does not exist.

### Step 3
Verify that the installation was successful by visiting the handler file in your browser. You should see a JSON response with the version number of the API and the name of your handler.

Assuming you have placed the platform you are using in your `public_html` folder and your API folder is called `smartcars`:
- If you're using phpVMS 5, the URL will be `http://yourdomain.com/smartcars/api/phpvms5/`.
- If you're using phpVMS 7, the URL will be `http://yourdomain.com/smartcars/api/phpvms7/`.

This URL will be the "Script URL" option in smartCARS 3 Central when managing your community.
