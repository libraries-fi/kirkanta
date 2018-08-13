# Kirjastohakemiston muutokset v3 -> v4

## Aukioloajat
### Omatoimiajat yhdistetty normaaleihin aukioloaikoihin
Omatoimisen asioinnin palveluajat ovat oleellinen osa aukioloja, joten ne omaan osastoonsa
eristämisen sijaan sulautettu varsinaisiin aukioloaikoihin. Tämän johdosta kenttä **closed** on
poistettu ja sen tilalle on tullut kenttä **status**, jolla on neljä arvoa:

1. **NULL** := Käsiteltävä aukiolotieto ei ole kuluvalta päivältä.
2. **0** := Kirjasto on suljettu juuri tällä hetkellä.
3. **1** := Kirjasto palvelee normaalisti juuri tällä hetkellä.
4. **2** := Kirjastossa on meneillään omatoimiaika / itsepalvelu.

Jokaisella yksittäisellä aikarivillä (taulukon **times** arvot) on kaksi uutta kenttää:
**staff** ja **active**.

Kenttä **staff** kertoo aikarivin osalta sen, onko henkilökunta paikalla kyseisenä aikana:
1. **TRUE** := Normaali aukiolo (henkilökunta paikalla)
2. **FALSE** := Omatoimiaika (henkilökunta ei paikalla; mahdollisesti rajoitettu pääsy)

Kenttä **active** kertoo, onko kyseinen aikatieto reaaliaikaisesti voimassa.

### Reaaliaikainen aukiolotieto
Kenttä **status** on pseudoreaaliaikainen. Sitä päivitetään tällä hetkellä 30 minuutin välein,
minkä pitäisi olla riittävä aikaväli todellisen aukiolotilanteen kartoittamiseksi.
