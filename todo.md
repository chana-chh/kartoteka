
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
