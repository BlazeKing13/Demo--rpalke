# STIMER – spletna stran

## Objava na GitHub Pages
1. Naložite vsebino te mape v GitHub repozitorij (index.html mora biti v korenu).
2. Settings → Pages → Source: *Deploy from a branch*, veja `main`, mapa `/ (root)`.
3. Settings → Pages → Custom domain: `www.stimer.si`, nato vklopite *Enforce HTTPS*.
4. Pri ponudniku domene nastavite DNS: CNAME `www` → `<uporabnik>.github.io`
   (za `stimer.si` brez www pa A zapise na GitHubove naslove iz njihove dokumentacije).

## Pošiljanje povpraševanja (FormSubmit)
Stran pošilja prek brezplačne storitve formsubmit.co, ker GitHub Pages ne izvaja strežniške kode.
1. Ko je stran objavljena, oddajte eno testno povpraševanje.
2. Na stimer.doo@gmail.com pride e-pošta FormSubmit za aktivacijo – kliknite *Activate Form*.
   (Testno povpraševanje se dostavi po aktivaciji, priloga iz tega prvega povpraševanja pa ne.)
3. FormSubmit nato pošlje naključni niz (alias). V `index.html` v CONFIG zamenjajte
   `formsubmitId: 'stimer.doo@gmail.com'` z nizom – tako e-naslov ni viden v kodi.
4. Prva sporočila preverite tudi v mapi *Vsiljena pošta* in jih označite kot *Ni vsiljena pošta*.

Lokalno odprta datoteka (file://) ne more poslati obrazca – preizkušajte na objavljeni strani.

## Alternativa: lastno gostovanje s PHP
Če stran gostite pri ponudniku s PHP (npr. obstoječe gostovanje domene stimer.si):
v CONFIG nastavite `mode: 'php'` in naložite tudi `send.php`. Takrat ne potrebujete
nobene zunanje storitve; podatki ostanejo na vašem strežniku.
Na GitHub Pages datoteka `send.php` ne deluje in jo lahko izpustite.

## Logotipi znamk
Datoteke SVG s prosojnim ozadjem v `assets/logos/` (glej PREBERI.txt).
