# Tabele

U tabeli `kartoni` dodati polje za ukupan saldo (+):

- saldo - decimal 12,2 = 0.00

Zakup razloziti na 10 godisnjih zaduzenja.

`zaduzenja`

- id - int 10 - unsigned
- karton_id - int 10 - unsigned
- tip - enum (taksa, zakup)
- godina - int 10 - unsigned = 2000
- iznos - decimal 12,2 = 0.00
- razduzeno - tinyint 4 = 0
- datum_zaduzenja - date
- datum_razduzenja - date = null
- referent_id_zaduzio - int 10 - unsigned
- referent_id_razduzio - int 10 - unsigned
- reprogram - tinyint 4 = 0

`racuni`

- id - int 10 - unsigned
- karton_id - int 10 - unsigned
- broj - varchar 100
- datum - date
- \*iznos - decimal 12,2 = 0.00
- razduzeno - tinyint 4 = 0
- datum_razduzenja - date = null
- referent_id_zaduzio - int 10 - unsigned
- referent_id_razduzio - int 10 - unsigned
- reprogram - tinyint 4 = 0

Ako ide samo racun onda mozda moze u zajednicku tabelu sa zaduzenjima,a ako se ide na stavke racuna:

`stavke_za_racun`

- id - int 10 - unsigned
- tip - enum (roba, usluga)
- naziv - varchar 255
- cena - decimal 12,2 = 0.00

`racun_stavke`

- id - int 10 - unsigned
- racun_id - int 10 - unsigned
- stavka_id - int 10 - unsigned
- kolicina - int 10 - unsigned
- \*iznos - decimal 12,2 = 0.00

`uplate`

- id - int 10 - unsigned
- karton_id - int 10 - unsigned
- datum - date
- iznos - decimal 12,2 = 0.00

`reprogram`

- id - int 10 - unsigned
- broj - varchar 100
- datum - date
- period - int 10 - unsigned
- iznos - decimal 12,2 = 0.00
- razduzeno - tinyint 4 = 0
- datum_razduzenja - date = null
- referent_id_zaduzio - int 10 - unsigned
- referent_id_razduzio - int 10 - unsigned

`reprogram_stavke`

- id - int 10 - unsigned
- reprogram_id - int 10 - unsigned
- tip - enum (zaduzenje, racun)
- stavka_id - int 10 - unsigned
- razduzeno - tinyint 4 = 0

`cene`

- id - int 10 - unsigned
- datum - date
- taksa - decimal 12,2 = 0.00
- zakup - decimal 12,2 = 0.00
- vazece - tinyint 4 = 0

# Pitanja

- ako staraoc ima vise kartona (reprogram, uplate, visak novca)
- gde staviti priznanicu
- na koji period se isplacuje reprogram (mesecno, kvartalno, godisnje)
- cenim da nije potrebno ici na stavke racuna. Racun bi trebalo da se placa ceo iznos, a ako i plati neki deo novac ce biti na saldu kartona pa kad doplati dovoljno onda razduziti seo racun

# TODO

- dodavanje cena uzima trenutni datum ...
-
