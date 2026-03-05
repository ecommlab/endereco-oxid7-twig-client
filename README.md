# Endereco Implementierung für Oxid 7

## Installation

Die Installation erfolgt in folgenden Schritten:

1. Das Modul über Composer installieren

`composer require endereco/endereco-oxid7-twig-client`

Der Befehl lädt die neuste Version herunter. Um eine spezielle Version zu installieren, zum Beispiel *1.0*, kann
der Befehl folgenderweise angepasst werden.

`composer require endereco/endereco-oxid7-twig-client:1.0`

2. Migrationen ausführen

`vendor/bin/oe-eshop-db_migrate migrations:migrate`

[Siehe Dokumentation für Migrationen in Oxid 7](https://docs.oxid-esales.com/developer/en/7.2/development/tell_me_about/migrations.html)

3. Modul aktivieren

`vendor/bin/oe-console oe:module:activate endereco-oxid7-client`

4. Konfiguration vom Modul im Admin-Bereich vornehmen
