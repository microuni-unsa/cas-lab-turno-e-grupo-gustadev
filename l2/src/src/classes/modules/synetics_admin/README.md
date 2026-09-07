# Add-on & Subscription Center

The admin-center add-on serves as endpoint for the 'Add-on & Subscription Center' placed in i-doit sfm.

## Build

Build frontend assets by executing `./build-release.sh --prod`. This script will install all node dependencies and build all resources.

## Development

The PHP backend can be changed as usual. The frontend javascript assets require running node.
Therefore go to `./js/` and run `npm run build`. Do not forget to install node dependencies before via `npm i`.

## Testing

## Use alternative AS-Center backend
For development you should/want to use another i-doit sfm instance. This instance can be local or you can also use our `testing` or `staging` instances.
To achieve this create file `src/.env` if it is not existing and define following parameter:
```dotenv
# Do not use trailing slash here
SFM_URL=http://my.different.center.link.de
```

## Use alternative license server

You can modify the license server url to use a different instance by setting `LICENSE_SERVER_URL` in your `src/.env`:
```dotenv
# Do not use trailing slash here
LICENSE_SERVER_URL=http://main.license-server.local
```

## Enforce usage of license token

It is also possible to enforce the usage of an specific `License Token` in `src/.env` despite the one which is setted up in your instance to test specific behaviour in AS-Center
without changing your own token:
```dotenv
# Use valid license tokens or fake tokens
SFM_LICENSE_TOKEN=your-license-token-to-be-used
```

