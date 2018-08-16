# SAHL

## Systémové požiadavky
Pripravíme si čistý ubuntu server 16.04

Potrebné balíky:

    sudo apt-get install apache2 php php-xml git libapache2-mod-php mysql-server

Aktivujeme potrebné apache moduly:

	sudo a2enmod rewrite ssl
	sudo service apache2 restart
	
## Postup pri inštalácii kopírovaním
Projekt nakopírujeme do `/var/www/sahl` s tým, že všetky súbory sú čitateľné pre 
používateľa `www-data` (alebo stačí lokálny používateľ), ideálne ak je ich vlastníkom.
Adresáre `temp` a `log` treba explicitne vytvoriť a musia byť pre užívateľa `www-data` aj zapisovateľné.

## Postup inštalácie z git repozitára
Ako používateľ `root` vytvoríme používateľovi `www-data` adresár pre ssh kľúče. 

    sudo mkdir /var/www/.ssh
    sudo chown www-data:www-data /var/www/.ssh

Používateľovi `www-data` vygenerujeme nový RSA kľúč. Všetky parametre potvrdíme 
bez zmeny (umiestnenie, názov, heslo).

    sudo -u www-data ssh-keygen -t rsa

Verejnú časť RSA kľúča - obsah súboru `id_rsa.pub` vložíme medzi deploy kľúče 
v danom repozitári. Aby sme mohli robit git operácie cez SSH a nezadávali stále heslo.

Vytvoríme adresár pre aplikáciu, vlastníkom bude používateľ `www-data`.

    sudo mkdir /var/www/sahl
    sudo chown www-data:www-data /var/www/sahl

Vyklonujeme repozitár ako používateľ `www-data`.

    sudo -u www-data -H git clone -b master git@bitbucket.org:alexandervalach/sahl.git /var/www/sahl

## Postup aktualizácie z git repozitára
Stiahneme aktuálne zdrojáky a zmažeme cache.

    sudo -u www-data -H git pull
    sudo rm -r /var/www/sahl/temp/cache

## Konfigurácia apache2
Do `/etc/apache2/sites-available/sahl.conf` pridáme nasledujúci obsah.

```
#!apacheconf

<VirtualHost *:80>
        
        DocumentRoot /var/www/sahl/www
		ServerName sahl

        <Directory "/var/www/sahl/www">
                AllowOverride All
        </Directory>

</VirtualHost>
```

Načítame a povolíme konfiguráciu sahl

    sudo a2ensite sahl.conf
    
Reštartujeme apache

    sudo service apache2 restart

Zadáme `sudo nano /etc/hosts` a pridáme nasledujúci riadok, kde X je ľubovoľné nezadané číslo od 0 - 255

    127.0.0.X sahl
    
Do prehliadača napíšeme http://sahl/
Ešte treba pridať databázu stiahnuteľnú cez websupport admin a `config.local.neon` do adresára `app/config`