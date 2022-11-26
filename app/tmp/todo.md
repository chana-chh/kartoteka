


# TODO


BRISANJE UPLATE:

- dodati stari datum_prispeca
- dodati staru glavnicu
- brisanje samo poslednje uplate


UPLATA

- datum uplate ne moze biti stariji od poslednje uplate





Zorica Simovic 060 234 1004


+ kod pretrage kartona za parcelu izbaciti LIKE (mozda i kod broja grobnog mesta)


# 1. MASOVNO ZADUZIVANJE KARTONA

+ dodati datum zaduzivanja (ponuditi trenutni datum)
+ dodati rok za kamatu / datum prispeca (-ponuditi na osnovu podesavanja)
+ postaviti ponudjenu godinu na proslu (zaduzuje se za proslu godinu na pocetku trenutne)

# 2. PREGLED ZADUZENJA I ZADUZIVANJE TAKSI I ZAKUPA

+ izmeniti metodu koja racuna iznos za razduzenje tako da racuna kamatu
+ kod pojedinacnog zaduzivanja takse i zakupa postaviti godinu zaduzenja na trenutnu
- datum zaduzenja je ponudjen trenutni datum
- dodati datum za kamatu (koji datum ponuditi obzirom da kad se automatski zaduzuju rok prispeca je od sledece godine)

- zaduzivanje racuna KAMATA ???

# 3. REPROGAMI

- sta se desava kad ne placa rate na vreme ???
- da li se racuna kamata (verovatno da) kod ubacivanja stavki u reprogram ?

# 4. RAZDUZENJE

- sta sa viskom para ???
- kako se placa unapred (cena i sl.) ?
- kad se delimicno razduzi staro zaduzenje treba da se resetuje datum za kamatu ?


- u klasi Config.php preuzeti podesavanja i iz nje preuzimati ista

- treba da se izbaci saldo / visak para se oduzima od glavnice

- u pregled zaduzenja treba dodati i glavnicu (trenutno zaduzenje)
- u opis dodati nova polja iz tabele zaduzenja


# 5. PITANJA


### Zaduzenja (taksa i zakup)

- da li postoji potreba da se prikaze detaljan obracun zatezne kamate
- da li je dozvoljena delimicna uplata
- ako jeste kako se regulise kamata
	(iznos i datum od kad se racuna kamata obzirom da kad se automatski zaduzuju rok prispeca je od sledece godine)
- od viska se skida kamata pa glavnica i resetuje se datum za kamatu ???
- zaduzivanje i razduzivanje u buducnost (datum od kada se racuna kamata)
	pretpostavljam da se sve sto se zaduzuje u buducnost automatski i razduzuje (placanje unapred) pa nema kamate


### Racuni

- da li je moguce da se ne plati racun (ili da se delimicno plati) i da li se onda racuna kamata


### Reprogram

- stavke ulaze u reprogram sa pripadajucom kamatom na dan zakljucenja reprograma
- sta se desava ako se rate ne placaku na vreme (kamata)



# AVANS


## PRAVILA ZA AVANS

Stanje avansa za staraoca moze da bude sledece:

1. avans = 0 i ukupanDug > 0 (duguje novac i nema avans)
2. avans = 0 i ukupanDug = 0 (ne duguje novac i nema avans)
3. avans > 0 i ukupanDug = 0 (ne duguje novac i ima avans)
4. avans > 0 i ukupanDug > 0 (duguje novac i ima avans)

1-3 su normalna stanja avansa

4 nije normalno stanje i potrebno je postojecim avansom razduziti nerazduzena zaduzenja i dovesti stanje na 1-3

Za stanje 4 onemoguciti transakcije (zaduzenje, razduzenje/uplata)
Pre masovnog zaduzivanja u bazi ne sme da postoji nijedan staraoc sa stanjem 4


KOD SVIH TRANSAKCIJA PROVERITI DA LI JE STARAOC AKTIVAN !!!


!!! VAZNO !!!

OSMISLITI KAKO DA SE UNESU ZADUZENJA I UPLATE IZ BAZE RACUNOVODSTVA
POCETNO STANJE

!!! VAZNO !!!
