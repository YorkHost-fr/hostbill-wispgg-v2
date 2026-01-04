# Module Wisp.gg - AUTO-FETCH Feature

## 🎯 Objectif

Cette modification ajoute la fonctionnalité **AUTO-FETCH** au module Wisp.gg pour HostBill. Le module récupère automatiquement les configurations depuis l'API Wisp.gg au lieu de nécessiter une saisie manuelle.

## ✨ Fonctionnalités AUTO-FETCH

Le module récupère automatiquement :

1. **Egg Variables** - Toutes les variables d'environnement avec leurs valeurs par défaut
2. **Startup Command** - La commande de démarrage du serveur
3. **Docker Image** - L'image Docker par défaut

## 🔧 Comment ça fonctionne

### Lors de la création d'un serveur (`Create()`)

1. Le module appelle `getEggWithIncludes()` qui fait une requête API :
   ```
   GET /api/admin/nests/{nest_id}/eggs/{egg_id}?include=variables
   ```

2. Le module analyse la réponse et extrait :
   - Les variables : `relationships.variables.data[]`
   - Le startup : `attributes.startup`
   - Le docker image : `attributes.docker_images` ou `attributes.docker_image`

3. Si les champs sont vides dans HostBill, le module utilise les valeurs auto-fetchées
4. Si les champs contiennent des valeurs, le module utilise ces valeurs (override manuel)

## 📋 Modifications apportées

### Nouvelles méthodes

1. **`getEggWithIncludes($nest_id, $egg_id)`**
   - Récupère les données complètes de l'egg avec les variables incluses
   - Utilise le paramètre `?include=variables` dans l'API

2. **`buildEggVariablesFromEgg($egg)`**
   - Construit automatiquement la chaîne de variables au format `VAR:value;VAR2:value2;`
   - Parse `relationships.variables.data[]` depuis la réponse API

### Méthodes modifiées

1. **`Create()`**
   - Utilise `getEggWithIncludes()` au lieu de `getEgg()`
   - Applique l'AUTO-FETCH pour variables, startup et docker image
   - Garde la compatibilité avec les valeurs manuelles (override)

2. **`ChangePackage()`**
   - Même logique AUTO-FETCH que `Create()`

### Descriptions mises à jour

Les descriptions des options dans `$options` ont été mises à jour pour indiquer le comportement AUTO-FETCH :

```php
'Egg variables' => [
    'description' => '[AUTO-FETCH] Egg variables will be fetched automatically from Wisp.gg. Manual override: variable:value;',
],
'Docker Image' => [
    'description' => '[AUTO-FETCH] Docker image will be fetched automatically from Wisp.gg. Leave empty to use egg default.',
],
'Startup script' => [
    'description' => '[AUTO-FETCH] Startup command will be fetched automatically from Wisp.gg. Leave empty to use egg default.',
],
```

## 📊 Logs de débogage

Le module génère des logs détaillés avec le préfixe `[AUTO-FETCH]` :

```
[2025-01-04 10:30:15] [AUTO-FETCH] Fetching egg with includes: nest=6, egg=6
[2025-01-04 10:30:15] [AUTO-FETCH] Successfully fetched egg: Ark: Survival Evolved
[2025-01-04 10:30:15] [AUTO-FETCH] Building egg variables from API data
[2025-01-04 10:30:15] [AUTO-FETCH] Found variable: SERVER_MAP = TheIsland
[2025-01-04 10:30:15] [AUTO-FETCH] Found variable: SESSION_NAME = My Server
[2025-01-04 10:30:15] [AUTO-FETCH] Built egg variables string: SERVER_MAP:TheIsland;SESSION_NAME:My Server;...
[2025-01-04 10:30:15] [AUTO-FETCH] Using egg docker image: quay.io/parkervcp/pterodactyl-images:debian_source
[2025-01-04 10:30:15] [AUTO-FETCH] Using egg startup: rmv() { echo -e "stopping server"; ...
```

## 🔄 Logique de priorité

Pour chaque champ (variables, startup, docker image) :

```
SI champ configuré manuellement dans HostBill :
    ✅ Utiliser la valeur manuelle [MANUAL]
SINON :
    ✅ AUTO-FETCH depuis l'API Wisp.gg [AUTO-FETCH]
```

## 📝 Exemple de réponse API

Exemple de structure de réponse pour un egg avec variables :

```json
{
  "object": "egg",
  "attributes": {
    "id": 6,
    "name": "Ark: Survival Evolved",
    "docker_images": {
      "quay.io/parkervcp/pterodactyl-images:debian_source": "quay.io/parkervcp/pterodactyl-images:debian_source"
    },
    "startup": "rmv() { ... }",
    "relationships": {
      "variables": {
        "data": [
          {
            "attributes": {
              "env_variable": "SERVER_MAP",
              "default_value": "TheIsland"
            }
          },
          {
            "attributes": {
              "env_variable": "SESSION_NAME",
              "default_value": "My ARK Server"
            }
          }
        ]
      }
    }
  }
}
```

## 🚀 Installation

1. **Sauvegardez votre fichier actuel** :
   ```bash
   cp /path/to/modules/servers/wispgg/class.wispgg.php /path/to/modules/servers/wispgg/class.wispgg.php.backup
   ```

2. **Remplacez le fichier** :
   ```bash
   cp class.wispgg_modified.php /path/to/modules/servers/wispgg/class.wispgg.php
   ```

3. **Vérifiez les permissions** :
   ```bash
   chown www-data:www-data /path/to/modules/servers/wispgg/class.wispgg.php
   chmod 644 /path/to/modules/servers/wispgg/class.wispgg.php
   ```

## ⚙️ Configuration

### Dans HostBill Admin

Lors de la création/modification d'un produit :

**Option 1 : AUTO-FETCH complet (recommandé)**
- Nest : Sélectionner le nest
- Egg : Sélectionner l'egg
- Egg variables : **LAISSER VIDE** ← AUTO-FETCH
- Docker Image : **LAISSER VIDE** ← AUTO-FETCH
- Startup script : **LAISSER VIDE** ← AUTO-FETCH

**Option 2 : Override manuel**
- Egg variables : `MA_VARIABLE:valeur_custom;AUTRE_VAR:autre_valeur;`
- Docker Image : `mon/image:custom`
- Startup script : `./custom_startup.sh`

## ✅ Avantages

1. **Aucune configuration manuelle** - Les variables sont récupérées automatiquement
2. **Toujours à jour** - Si l'egg change dans Wisp.gg, les nouvelles variables sont utilisées
3. **Moins d'erreurs** - Pas de risque de typo dans les noms de variables
4. **Override possible** - On peut toujours forcer des valeurs manuelles si nécessaire
5. **Logs détaillés** - Debug facile avec les logs `[AUTO-FETCH]`

## 🔍 Vérification

Pour vérifier que l'AUTO-FETCH fonctionne :

1. Activez le debug : `WISP_DEBUG_ENABLED = true`
2. Créez un serveur de test
3. Consultez `/tmp/wisp_debug.log`
4. Cherchez les lignes `[AUTO-FETCH]`

## 🐛 Troubleshooting

**Problème** : Pas de variables récupérées
- **Solution** : Vérifiez que l'egg a bien des variables configurées dans Wisp.gg
- **Vérification** : Consultez les logs pour voir `[AUTO-FETCH] No variables found in egg data`

**Problème** : Mauvaise image Docker
- **Solution** : L'API retourne soit `docker_image` (string) soit `docker_images` (object)
- **Vérification** : Le module gère les deux cas automatiquement

**Problème** : Variables non parsées
- **Solution** : Vérifiez le format dans les logs : `VAR:value;VAR2:value2;`
- **Note** : Pas d'espace autour du `:` ou du `;`

## 📌 Notes importantes

- Le module est **rétrocompatible** : les valeurs manuelles fonctionnent toujours
- Les variables sont au format : `ENV_VAR:default_value;ENV_VAR2:value2;`
- Le module gère les eggs sans variables (pas d'erreur)
- Version du module : `1.0.2` (vs `1.0.1` originale)

## 🎉 Résultat

Avec cette modification, vous pouvez :
- Créer un produit dans HostBill
- Sélectionner Nest + Egg
- Laisser les champs vides
- Le module fera tout le reste automatiquement !

Plus besoin de copier-coller les variables depuis Wisp.gg vers HostBill ! 🚀
