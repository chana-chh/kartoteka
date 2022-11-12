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

