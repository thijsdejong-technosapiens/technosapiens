# WordPress livegang checklist

## 0. Doel en uitgangspunten

Het live zetten van een website is een cruciale stap in het ontwikkelproces. Deze checklist zorgt ervoor dat alle onderdelen correct functioneren, fouten tot een minimum beperkt blijven en de site veilig, stabiel en betrouwbaar gelanceerd wordt.

**Uitgangspunten**

- De codebase is correct en getest op staging.
- De uploads zijn juist gesymlinkt en de database is klaar voor overzetten.
- Gebruik `voorbeeld.nl` / `staging.voorbeeld.nl` hieronder als placeholder voor het echte project.

**Verantwoordelijke:** _[naam developer]_ · **Project:** _[projectnaam]_ · **Geplande livegang:** _[datum]_

---

## 1. Voorbereiding (vóór de livegang)

### 1.1 Domein en hosting

- [ ] Domein is geregistreerd en in beheer
- [ ] Domeininstellingen (DNS, nameservers) zijn bekend en klaar om aan te passen
- [ ] Live serveromgeving is aangemaakt (PHP-versie gelijk aan staging)
- [ ] Automatische back-ups staan aan in Plesk (of het hostingpanel)

### 1.2 Content

**Dummy content verwijderen**

- [ ] Zoek op de zoekresultatenpagina naar bijv. "Lorem ipsum" en controleer of er geen vreemde content terugkomt
- [ ] Steekproef: geen lege pagina's (wel? → consultant of klant inlichten)
- [ ] Demo-/testpagina's verwijderd (bijv. de pagina met alle voorbeeldblokken)
- [ ] (Optioneel) Gravity Forms-testinzendingen verwijderd:
  ```bash
  wp gf entry delete --all --force
  ```
  > ⚠️ **Let op:** dit commando is permanent en onomkeerbaar. Maak altijd eerst een back-up van de database.

**Kwaliteit**

- [ ] Gecontroleerd op spel- en grammaticafouten
- [ ] Alle links werken (geen verwijzingen meer naar staging of `.test`)
- [ ] Afbeeldingen en media zijn geoptimaliseerd voor web (juiste formaten/thumbnails)
- [ ] 404-pagina is ingesteld en gevuld met content

**Fonts**

- [ ] Bij gebruik van Adobe Fonts (TypeKit) of Font Awesome Pro: live domein toegevoegd aan de kit

### 1.3 Ontwerp en functionaliteit

- [ ] Mobiele responsiviteit getest
- [ ] Getest in verschillende browsers (Chrome, Safari, Firefox, Edge)
- [ ] Favicon / site-icoon ingesteld
- [ ] Google Lighthouse gedraaid op de belangrijkste templates (performance, accessibility, SEO)
- [ ] Bij een nieuwe versie van een bestaande website: gebruikersrollen en -rechten nagelopen (oude omgeving uit, nieuwe omgeving aan)

### 1.4 SEO

- [ ] Pagina's die niet geïndexeerd hoeven te worden staan op **noindex** via Yoast SEO (bijv. bedankpagina's, blokkenoverzicht)
- [ ] Auteursarchieven uitgeschakeld in de sitemap:
  _SEO > Zoekweergave > Archieven > Auteursarchieven > Uitgeschakeld_
- [ ] XML-sitemap bevat de juiste post types en taxonomieën
- [ ] Meta titles en descriptions ingevuld voor de belangrijkste pagina's
- [ ] Structured data gecontroleerd waar relevant (bijv. vacatures, FAQ, organisatie)
- [ ] **robots.txt**: standaard via Yoast SEO. Vraag de klant of er specifieke regels bij moeten; deze kunnen via de Thema-opties worden toegevoegd.

### 1.5 Analytics, tracking en cookies

- [ ] Google Tag Manager, Meta-pixel e.d. correct ingesteld in de plugin **Metronet Tag Manager**
- [ ] Google Analytics loopt via Tag Manager
- [ ] Cookiebanner (Cookiebot) toegevoegd en live domein toegevoegd in het Cookiebot-account
- [ ] Deelknoppen voor social media aanwezig waar gewenst (bijv. bij blogs)

### 1.6 Wettelijke naleving

- [ ] Privacyverklaring aanwezig
- [ ] Algemene voorwaarden aanwezig (indien van toepassing)
- [ ] Cookieverklaring aanwezig en gekoppeld aan de cookiebanner

### 1.7 Migratieplan en redirects

- [ ] Met de klant afgestemd of er een migratieplan met redirects nodig is

Redirects vooraf invoeren in de plugin **Redirection** voorkomt 404-fouten bij gewijzigde URL's, behoudt de SEO-waarde van oude pagina's en houdt interne links werkend.

**Redirect handmatig aanmaken**

1. Ga naar **Gereedschap > Redirection** (eerste keer: doorloop de korte setup en klik op **Voltooien**).
2. Open de tab **Redirects** en klik op **Nieuwe toevoegen**.
3. Vul in:
   - **Bron-URL**: de oude URL (bijv. `/oude-pagina/`)
   - **Doel-URL**: de nieuwe URL (bijv. `/nieuwe-pagina/`)
   - **Groep**: *Redirections* (standaard)
   - **Match**: *URL alleen*, tenzij een geavanceerde regel nodig is
   - **Actie**: *Omleiding naar URL*
   - **Type**: *301 (permanent)*, of 302 voor een tijdelijke omleiding
4. Klik op **Omleiding toevoegen**.

**Lijst importeren (CSV)**

```
/oude-pagina/, https://voorbeeld.nl/nieuwe-pagina/, 301
/verouderde-url/, https://voorbeeld.nl/nieuwe-url/, 302
```

💡 **Belangrijk**

- Geen kopregel, alleen data.
- Gebruik bij interne redirects geen volledige domeinnaam in de Bron-URL (`/oude-pagina/`, niet `https://voorbeeld.nl/oude-pagina/`).
- `301` = permanent, `302` = tijdelijk.

1. Klik op **Bestand kiezen** en selecteer het `.csv`-bestand.
2. Klik op **Uploaden** en controleer de preview.
3. Klik op **Importeren**.

**Redirects testen**

- [ ] Oude URL's getest in een incognitovenster
- [ ] Statuscodes gecontroleerd met een HTTP-statuschecker (bijv. [httpstatus.io](https://httpstatus.io/))

### 1.8 WordPress en CMS

- [ ] WordPress geüpdatet naar de nieuwste versie
- [ ] Alle plugins geüpdatet naar de nieuwste versie
- [ ] Tijdzone ingesteld op **Amsterdam** (Instellingen > Algemeen)
- [ ] Permalinks geflusht
- [ ] Standaard beheerdersaccount van het bureau is aanwezig en correct (bijv. gebruikersnaam `beheer` met `wordpress@voorbeeld.nl`)
- [ ] Gebruikersrechten van de klant gecontroleerd (juiste rollen, geen overbodige admins)
- [ ] Server- en CMS-inloggegevens staan compleet in de wachtwoordmanager, voor alle varianten: `voorbeeld.test`, `staging.voorbeeld.nl` en `voorbeeld.nl`

### 1.9 Git en deployment

**Git**

- [ ] Correcte Git-structuur en branches
- [ ] `.gitignore` is correct (geen uploads, `.env`, `node_modules` e.d. in de repo)

**README.md** is up-to-date:

- [ ] Links naar de repository
- [ ] Titel en omschrijving van het project
- [ ] Lokale setup (bijv. Valet)
- [ ] Juiste PHP- en Node.js-versie

**.env** van de master/productie is correct ingevuld. Let vooral op de eerste drie variabelen:

```
DEV=false
CACHE=true
INDEXABLE=true

DB_NAME=xxxx
DB_USER=xxxx
DB_PASSWORD="xxxx"
DB_HOST=xxxx
DB_PREFIX=xx_

LICENSE_KEY_ACF=xxxx
LICENSE_KEY_GF=xxxx
LICENSE_KEY_AKISMET=xxxx
LICENSE_KEY_WPMUDEV=xxxx
```

**Pipeline (bijv. Jenkins)**

- [ ] Master-pipeline deployt naar de juiste (nieuwe) server
- [ ] Target staat op de juiste map

### 1.10 API-koppelingen en importers

- [ ] Koppelingen met derden (nieuwsbrief, importers, externe API's) werken
- [ ] Autorisaties staan goed en keys zijn toegevoegd aan de juiste `.env`-bestanden

---

## 2. De livegang

### 2.1 Symlink goedzetten op live

```bash
# Als de map "public_html" al bestaat, verplaats deze eerst
mv public_html public_html.bak

# Maak de symlink voor public_html
ln -s build-master/current/public public_html

# Werkt het niet? Draai de wijziging terug met de back-up
mv public_html public_html.bak2
mv public_html.bak public_html
```

- [ ] Symlink staat goed

### 2.2 Uploads overzetten naar de shared map

- [ ] Laatste `pullmedia` van de staging-omgeving binnengehaald
- [ ] Uploads staan in de shared map en alle afbeeldingen laden

### 2.3 Database export, import en search-replace

> 🚨 **Vóór de export:** zet de auteur van alle pagina's en posts op een account van de klant. Een developer mag geen auteur zijn van content op de live website! 🚨

- [ ] Auteurs aangepast naar de klant
- [ ] Staging-database geëxporteerd (bijv. met Sequel Ace)
- [ ] Database geïmporteerd op de live omgeving
- [ ] Via SSH naar de WordPress-installatie op productie en staging-URL's vervangen. Eerst een dry-run:
  ```bash
  wp search-replace staging.voorbeeld.nl voorbeeld.nl --all-tables --dry-run
  ```
  Klopt het resultaat? Voer het commando dan opnieuw uit zonder `--dry-run`.

### 2.4 DNS en zichtbaarheid

De DNS zorgt ervoor dat het domein naar het juiste IP-adres van de server verwijst.

- [ ] DNS-records (A/AAAA, `www`) aangepast naar de nieuwe server
- [ ] DNS-propagatie gecontroleerd via [dnschecker.org](https://dnschecker.org/)

**DNS nog niet doorgevoerd?** Simuleer het lokaal met **Gasmask** (of via het `hosts`-bestand), zodat je de site al kunt testen zonder effect op andere gebruikers:

```
203.0.113.10 voorbeeld.nl
203.0.113.10 www.voorbeeld.nl
```

- [ ] Content, afbeeldingen en links laden correct op het live domein

> Houd er rekening mee dat op sommige servers direct na het instellen niet alle functionaliteit meteen werkt (bijv. het aanmaken van een post).

### 2.5 SSL en omleidingen

- [ ] SSL-certificaat (Let's Encrypt) aangevraagd, pas nadat de DNS goed staat
- [ ] Website wordt altijd via **HTTPS** geserveerd (HTTP → HTTPS-redirect)
- [ ] Non-www → www (of andersom, volgens voorkeur) doorgestuurd
- [ ] Geen mixed-content-meldingen in de browserconsole

### 2.6 Afbeeldingen

- [ ] **Image helper**: indien gebruikt, consultant/klant informeren welke afbeeldingen extra aandacht nodig hebben voor de performance
- [ ] **Thumbnails geregenereerd** (nieuwe afbeeldingsformaten tijdens development):
  ```bash
  # Alleen ontbrekende formaten genereren
  wp media regenerate --only-missing

  # Alle formaten opnieuw genereren zonder bevestiging
  wp media regenerate --yes
  ```
- [ ] **Smush Pro**: lokale WebP-conversie staat aan en instellingen gecontroleerd
  > ⚠️ Pas op met de instelling **Ultra**: deze kan afbeeldingen erg onscherp maken.

### 2.7 Caching en performance

- [ ] **Autoptimize** geconfigureerd volgens de standaardconfiguratie
- [ ] **Comet Cache** geactiveerd
- [ ] Comet Cache expiration time aangepast van **7 dagen** naar **28 dagen**
- [ ] Lighthouse opnieuw gedraaid op live

### 2.8 Yoast SEO herindexeren

Zorgt dat alle URL's en SEO-gegevens kloppen voor het live domein.

- [ ] Uitgevoerd:
  ```bash
  wp yoast index --reindex
  ```

### 2.9 E-mail versturen (WP Mail SMTP)

WordPress verstuurt mail via `wp_mail`, een wrapper rond PHPMailer. Zonder SMTP-dienst gaat alle mail via de mailclient op de server. Grote partijen zoals Google en Microsoft accepteren vaak alleen mail van bekende IP-adressen, waardoor servermail in de spam belandt of niet aankomt. Daarom heeft een externe SMTP-dienst (bijv. Postmark) de voorkeur: betrouwbaar, lage kosten en in 15-30 minuten ingericht.

- [ ] WP Mail SMTP gekoppeld aan de SMTP-dienst (indien afgenomen)
- [ ] Benodigde DNS-records (SPF, DKIM, Return-Path) aan de klant/DNS-beheerder doorgegeven en actief
- [ ] Testmail verstuurd en gecontroleerd met [mail-tester.com](https://www.mail-tester.com/)
- [ ] Algemene mailinstellingen gecontroleerd (afzendernaam, afzenderadres, `{admin_email}`)

### 2.10 Formulieren (Gravity Forms)

**Testinzending**

- [ ] Elk formulier zelf ingevuld en verzonden
- [ ] Validatie werkt (verplichte velden, foutmeldingen)
- [ ] Inzending staat in **Formulieren > Inzendingen**

**Notificaties**

- [ ] Notificaties correct ingesteld via **Formulierinstellingen > Notificaties**
- [ ] Afleveradres staat op het e-mailadres van de klant (niet meer op een developer-adres)
- [ ] Mail komt aan (ook spamfolder gecheckt, getest met een ander e-mailadres)

**Anti-spam**

- [ ] Akismet-add-on gekoppeld
- [ ] Google reCAPTCHA-add-on gekoppeld en werkend op het live domein

**Debuggen bij problemen**

- Zet logging aan via **Formulieren > Instellingen > Logboekregistratie** (voor inzendingen en e-mail) en bekijk na een testinzending de logs.

### 2.11 Cronjobs

- [ ] Server-cronjob voor `wp-cron` ingesteld (en `DISABLE_WP_CRON` waar van toepassing)
- [ ] Eventuele importer-/sync-cronjobs ingesteld

### 2.12 API-koppelingen en importers (opnieuw)

- [ ] Koppelingen met derden werken **op live** en autorisaties staan goed
- [ ] Keys staan in de juiste `.env`-bestanden

---

## 3. Na de livegang en nazorg

- [ ] Geen "noindex" meer op de site: controleer de response header (`X-Robots-Tag`) én de meta robots-tag
- [ ] Google Search Console ingesteld en domein geverifieerd
- [ ] Sitemap ingediend in Google Search Console
- [ ] Analytics/Tag Manager ontvangt data (realtime-rapport of Tag Assistant)
- [ ] Brokenlinkscan gedraaid op het live domein
- [ ] Website toegevoegd aan uptime-monitoring (bijv. UptimeRobot of StatusCake)
- [ ] Website toegevoegd aan de interne monitoring/onderhoudstool
- [ ] Back-ups draaien op de live omgeving
- [ ] Oude omgeving (indien van toepassing) uitgezet of afgeschermd
- [ ] Klant en consultant geïnformeerd dat de site live is
