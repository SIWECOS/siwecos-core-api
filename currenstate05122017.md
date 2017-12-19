# Aktueller Stand Siweocs Core Api

## 05.12.2017

### CoreApi Definition

Die Core Api ist zur Zeit mit 3 Hauptfunktionalitäten ausgestattet.

- Token
    - über den Tokenendpunkt werden abstrakte Token mit einem ACL Level und 
    einer digitalen Währung (credits verwaltet). Dieser Token wird für alle Operationen bzgl. 
    Domains und Scans benötigt. Die Realisierung der Überprüfung findet über eine Middleware statt, welche in 2 Stufen arbeitet.
        1. Überprüfung ob Token vorhanden und gültig ist
        1. Überprüfung ob genügend Credits vorhanden sind
    - Um einen neuen Token generieren zu können, wird der sogenannte Mastertoken benötigt. 
    Dieser wird über folgenden Code erstellt. Dieser enthält ein virtuelles ACL Level von 9999, 
    welches sich nicht über die API einstellen lässt. Somit kann kein "Mastertoken" über die API generiert werden.
    - weitere Funktionen welche über den Token endpunkt verfügbar sind
        - setCredits - Anzahl der virtuellen Credits für den Token einstellen
        - revoke - Token löschen und somit alle vebundenen Domains löschen
    
```
$ php artisan create:mastertoken
```
- Domain
    - über den Domainendpunkt können die verknüpften Domnains verwaltet und hinzugefügt werden. Die Verknüpfung der Domains erfolt über die 
    persönlichen Tokens, welche über den "Token" Endpunkt erstellt werden. Die Validierung des Tokens erfolgt über Middlewares, die
        1. den Token auf validät überprüfen
        1. Definition des "scanDangerLevels"
    - nachdem eine Domain hinzugefügt wurde muss diese "Inhaber" validiert werden. Diese geschieht über folgende Möglichkeiten
        1. METATAG (siweocsToken) - Dieser muss mit dem generierten "DomainToken" (24 stellige Alphanum Zeichenkette) in den Head
        der zu validierenden Seite geschrieben werden
        1. FILE - ein File mit dem Dateinamen "domaintoken".html und dem Inhalt "domaintoken" muss im Rootbereich der Website erstellt werden.
        Dieses verhalten wurde, dem Google Mechanismus nachgestellt
    - über den Endpunkt "/domains" können alles verknüpften Domains des userTokens abgefragt werden, hier wird ebenfalls der Status mit 
    zurückgegeben
    - ein Endpunkt zum entfernen von Domains ist ebenfalls implementiert

- Scan
    - über den Scanendpunkt können die grundlegenden Funktionen des eigentlichen Scanvorgangs gesteuert werden. 
        - Hinzufügen eines Scanauftrags in die Queue
        - Das DangerLevel wird hierbei anhand des Domainlevels bestimmt, um gewisse intensive Tests ausschließen zu können
        - der Statusendpunkt gibt Informationen zum aktuellen Scanvorgang zurück (running / finished)
        - Zur Ergebnisanzeige werden 2 Endpunkte zur Verfügung gestellt
            1. RawResult, welche eine Art Basisantwort zu eigenständigen weiteren Verarbeitung bereitstellt 
            1. Result, welche eine beautfied / rated Antwort zur Verfügung gestellt (Das Schema dieser Antwort wird 
            weiter unten in diesem Dokument diskutiert)
        

### Workflow

Zur initialen Verwendung werden folgende Schritte benötigt. Die Beispiele gehen von einer Unix basierten Umgebung aus
- herunuterladen des aktuellen GitRepos
```
$ git clone https://github.com/SIWECOS/siwecos-core-api.git
$ cd siweocs-core-api/CoreApi
```
- initiales "composer install"
```
$ composer intstall
```

- Erstellung des Environment Files
```
$ cp .env.example .env
$ nano .env
```

- Erstellung des Appkeys
```
$ php artisan key:generate
```

- Ersrtellung des Masterkeys (für die Verwendung des Tokenendpunkts)
```
$ php artisan create:mastertoken
```

### JSON Antwort Definition

- To be discussed 

### Internationalisierung

- Die Internationaliserung der zurückgegebenen JSON Files erfolgt ebenfalls durch bereitgestellte JSON Files ("lang/en.json")