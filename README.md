# ZOCO

Plugin pour le portail [Karambol](https://github.com/Bornholm/karambol)

## Installation
Après avoir suivi les instructions [ici](https://github.com/Bornholm/karambol/blob/develop/doc/fr/plugins/create-plugin.md) pour installer le plugin, suivez les instructions suivantes à la racine du projet karambol :
```bash
# publication de la configuration
script/console karambol:plugin:config bornholm/zoco

# migration de la base de données
script/migrate

# creation de l'index elasticsearch
script/console zoco-plugin:index:create

# récupérer les archives
script/console zoco-plugin:boamp:fetch-archives

# extraire les archives
script/console zoco-plugin:boamp:extract-archives

# parser les sources pour les stocker
script/console zoco-plugin:boamp:parse-xml

# mise à jour du mapping
script/console zoco-plugin:index:update-mappings

# installation des assets
script/console karambol:link-assets
```

## Licence

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/).
