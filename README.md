# Steckbriefänderungen 
Das Plugin erlaubt es Nutzern den Steckbrief eigenständig zu editieren und somit zur Prüfung durch ein Teammitglied freizugeben. Diese werden über einen Banner darüber informiert und sehen die Änderungen in dem Steckbrief in einer gegenüberstellenden Tabelle.

## Funktionen
__allgemeine Funktionen__
* User können ihren Steckbrief editieren und abschicken
* Wenn eine Änderung noch nicht angenommen ist, sehen User beim Editieren, dass dies aktuell nicht möglich ist, da noch ungeprüfte Änderungen vorliegen
* User werden bei Annahme über einen Alert informiert
* User werden bei Ablehnung über eine PN informiert, wo der abgelehnet Steckbrief enthalten ist

__Funktionen für Admins__
* Festlegung für Bereiche in welchen sich Steckbriefe befinden
* Festlegung, ob Teammitglieder ihre eigenen Steckbriefänderungen annehmen können
* Annahme von Änderungen
* Ablehnung von Änderungen mit einem Grund

## Externe Bibliothek
thanks to Kate Rose Morley for the diff implementation [source](https://code.iamkate.com/php/diff-implementation/)

## Voraussetzungen
keine

## Template-Änderungen
Zusätzlich wird ein CSS-File mit dem Namen editprofile.css angelegt

__Neue Templates:__
* `editprofile_banner`
* `editprofile_misc_overview`
* `editprofile_modcp`
* `editprofile_modcp_bit`
* `editprofile_modcp_nav`
* `editprofile_postbit_edit`
* `editprofile_unapprovededit`

__Veränderte Templates:__
* `header` (wird um die Variablen `$steckichangesbanner` erweitert)
* `modcp_nav_users` (wird um die Variable `$nav_editprofile` erweitert)

## Vorschaubilder
__Ansicht in der Einstellungen__
![settings](https://aheartforspinach.de/upload/plugins/editprofile-settings.png)

__Ansicht im ModCP__
![modcp](https://aheartforspinach.de/upload/plugins/editprofile-modcp.png)

__Ansicht der Änderungen (misc-Seite)__
![misc](https://aheartforspinach.de/upload/plugins/editprofile-misc.png)
