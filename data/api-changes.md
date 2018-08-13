phone vs. phone_number??
email vs. email_address??

Headereita rajapintamuutoksista ilmoittamiseen?
- deprekoidut kentät
- uuden rajapintaversion julkaisu
- ???

Poista 'refs' – yhdistä yksinkertaistettuna with-parametriin?

**Kuvat**
Kuvatiedoston resoluutio rajapintaan?







# Kirkanta-rajapinnan muutokset v3 -> v4
Rajapinnan päivityksen taustalla on päällimmäisenä syynä Kirkannan tietomallin muuttuminen. Samalla on tehty joitakin pienempiä muutoksia esimerkiksi kenttien nimeämisen yhtenäistämiseksi sekä (uusien) teknisten rajoitteiden johdosta.

## Käytetyt termit
Termi | Selite
----- | ------
nimitietue | Sanakirja, joka sisältää kentän arvot eri kielillä (tai kun kyselyssä kielikoodi, kyseisen kieliversion arvon merkkijonona)

## Rikkovat muutokset

### Parametrien validointi
Uusi rajapinnan versio asettaa joitakin ehtoja kyselyissä käytettäville parametreille.

1. Vain dokumentoidut parametrit on sallittu.
2. Parametreille tehdään tyyppitarkistus.
3. Mikäli parametrille on määritetty sallittujen arvojen joukko, sekin tarkistetaan (esim. *with*-parametri)

Validoinnin epäonnistuessa palvelin vastaa virhekoodilla **400 Bad Request**.

### Parametrien nimeäminen on muuttunut
Parametrien nimissä käytetyt pisteet on muutettu kaksoispisteiksi
```
*v3* ?city.name=helsinki,espoo
*v4* ?city:name=helsinki,espoo
```

### Tietue sisältää vain ne käännökset, jotka sille on eksplisiittisesti lisätty
Jatkossa tietueille on erikseen lisättävä kieliversio ylläpidosta. Muutos koskee myös rajapintaa, sillä se palauttaa enää ne kieliversiot, jotka tietueelle on oikeasti lisätty. Aiemmin rajapinta yritti näyttää arvot kaikille kielille, jotka Kirkannassa oli mahdollista syöttää huolimatta siitä, oliko tietuetta oikeasti käännetty kyseisille kielille.

### Kieliversiot hakutuloksissa
Kun kyselyyn lisätään kieliparametri *lang*, tulosjoukkoon sisältyvät vain ne tietueet, joille on lisätty kyseinen käännös. Edellinen rajapinnan versio palautti kaikki tietokannassa olevat (muihin hakuehtoihin täsmänneet) tietueet ja korvasi puuttuvat käännökset suomenkielisillä arvoilla.

## Tyyppikohtaiset muutokset
### Organisaatiot / kirjastot (organisation)
1. address.city on kokonaisen kuntatietueen sijaan enää *nimitietue*.
2. Kenttä 'coordinates' on jaettu coordinates.lat- ja coordinates.lon-kentiksi.
3. Järjestämiseen käytetyt kentät 'city.name' yms. poistettu -> jatkossa vain 'city', 'consortium' etc. (järjestys nimen perusteella)
4. Järjestysoptio 'region' poistettu kokonaan.
5. Kenttä 'web_library' on poistettu; käytä jatkossa kenttää consortium.homepage.

### Kirjastokimpat (consortium)
1. Parametri *special* on nyt *finna:special*. Sallitut arvot ovat **0** (kirjastoalan kimpat), **1** (kirjastoalan ulkopuoliset Finna-organisaatiot) ja **any** (mikä tahansa).

### Henkilökunta (person)
1. Kenttä *phone* on nyt *phone_number*
