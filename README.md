# Groupe 8

## Membres

- FRANCISCO-LEBLANC Sacha
- PANIK Nikola
- SABER Mehdi

## Installation
1) ``composer install && npm i``
2) Vérifier que `.env` existe, sinon copier ``.env.example`` dans `.env`
3) ``./vendor/bin/sail up -d --build``
4) ``./vendor/bin/sail artisan migrate:fresh --seed``
