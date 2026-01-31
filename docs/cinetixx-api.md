# Cinetixx API

The API endpoint at `http://services.cinetixx.eu/Services/CinetixxService.asmx/GetShowInfo` response is an
array of the following XML-Element.

```xml

<Show id="3412043887" status="SHOW_ENABLED">
    <SHOW_BEGINNING>2026-01-23T16:45:00+01:00</SHOW_BEGINNING>
    <SHOW_END>2026-01-23T18:30:00+01:00</SHOW_END>
    <SHOW_ID>3412043887</SHOW_ID>
    <TEXT>
        Die Sommerferien haben begonnen und Die drei ??? wollen einen Roadtrip unternehmen. Doch gerade als Justus Jonas
        (JULIUS WECKAUF), Peter Shaw (NEVIO WENDT) und Bob Andrews (LEVI BRANDL) aufbrechen wollen, klingelt in der
        Zentrale das Telefon und ein unbekannter Anrufer übergibt dem Detektiv-Trio ihren neuesten Fall. Da sind die
        Urlaubspläne natürlich schnell vergessen. Die drei Jungs verfolgen die Spuren und stoßen auf den Geheimbund
        Sphinx rund um den mysteriösen Archäologie-Professor Phoenix (ANDREAS PIETSCHMANN) und seinen Assistenten Olin
        (JANNIK SCHÜMANN). Sphinx führt illegale Ausgrabungen durch und verkauft die so gestohlenen Kunstschätze. Bald
        starten sie eine Expedition zu der aktiven Vulkaninsel Makatao, die auch als die Toteninsel bekannt ist. Denn
        die dort gelegene Ruhestätte eines uralten Volkes soll mit einem Fluch belegt sein: Niemand, der Makatao
        betritt, kommt lebend zurück. Warum begibt sich Sphinx auf eine so waghalsige Reise? Und was hat der
        erfolgreiche Unternehmer Joseph Saito Hadden (SIMON KLUTH) mit der Expedition zu tun?
    </TEXT>
    <VERKAUFSSTART>2026-01-19T20:41:24+01:00</VERKAUFSSTART>
    <VERKAUFSENDE>2026-01-23T16:30:00+01:00</VERKAUFSENDE>
    <RESERVIERUNGSSTART/>
    <RESERVIERUNGSENDE/>
    <BOOKING_LINK>
        https://booking.cinetixx.de/frontend/index.html?cinemaId=808957959&showId=3412043887&bgswitch=false&resize=false
    </BOOKING_LINK>
    <MOVIE_ID>3251606397</MOVIE_ID>
    <EVENT_ID>3403758405</EVENT_ID>
    <ARTWORK>
        https://images.cinetixx.com/posters/3347861243/thumbnails/3347861243.jpg
    </ARTWORK>
    <IMAGE_1>
        https://images.cinetixx.com/scenes/3251606199/thumbnails/3251606199_283_172.jpg
    </IMAGE_1>
    <IMAGE_2>
        https://images.cinetixx.com/scenes/3251606202/thumbnails/3251606202_283_172.jpg
    </IMAGE_2>
    <IMAGE_3>
        https://images.cinetixx.com/scenes/3251606205/thumbnails/3251606205_283_172.jpg
    </IMAGE_3>
    <VERANSTALTUNGSTITEL>Die Drei ??? - Toteninsel</VERANSTALTUNGSTITEL>
    <VERANSTALTUNGSKURZTITEL>Die Drei ??? - Toteninsel</VERANSTALTUNGSKURZTITEL>
    <SPRACHVERSION>D</SPRACHVERSION>
    <ALTERSFREIGABE>ab 6</ALTERSFREIGABE>
    <CITY_ID>808957619</CITY_ID>
    <STADT>Cottbus</STADT>
    <CINEMA_ID>808957959</CINEMA_ID>
    <KINO>Filmtheater Weltspiegel</KINO>
    <AUDITORIUM_ID>808958079</AUDITORIUM_ID>
    <SAAL>Saal 2</SAAL>
    <REGION_ID>808957117</REGION_ID>
    <REGION>Brandenburg</REGION>
    <SPIELDAUER_EVENT>104</SPIELDAUER_EVENT>
    <MANDATOR_ID>758441768</MANDATOR_ID>
    <MOVIE_LINK>https://youtu.be/EXLefR_IfU0?si=DwAJapSLPrCDYE-m</MOVIE_LINK>
    <EDI_NR>2023902</EDI_NR>
    <XREFTITLENO>244541</XREFTITLENO>
    <XREFRELEASENO>2023902</XREFRELEASENO>
    <FLAG_3D>false</FLAG_3D>
    <VERSIONTYPE>D</VERSIONTYPE>
    <ASPECTRATIO>TBA</ASPECTRATIO>
    <LANGUAGE>D, Deutsch</LANGUAGE>
    <AUDIOTYPE>TBA</AUDIOTYPE>
    <SEATSELECTION>true</SEATSELECTION>
    <ARTWORK_BIG>
        https://images.cinetixx.com/posters/3347861243/thumbnails/3347861243.jpg
    </ARTWORK_BIG>
    <STARTDAY>2026-01-10T00:00:00+01:00</STARTDAY>
    <CATEGORIES/>
    <EVENT_TRAILER>https://youtu.be/EXLefR_IfU0?si=DwAJapSLPrCDYE-m</EVENT_TRAILER>
    <YEAR>2025</YEAR>
    <COUNTRY>DE</COUNTRY>
    <GENRE>Abenteuer</GENRE>
    <ACTOR>Julius Weckauf, Nevio Wendt, Levi Brandl</ACTOR>
    <DIRECTOR>Tim Dünschede</DIRECTOR>
    <SCREENWRITER></SCREENWRITER>
    <MUSIC></MUSIC>
    <CAMERA></CAMERA>
    <TYPE Key="SHOW_TYPE_STD">Standard</TYPE>
    <PRICE>Fr/Sa/So</PRICE>
    <TEXT_SHORT></TEXT_SHORT>
    <PRICES/>
    <!--
    STATUS: SHOW_ENABLED - Freigegeben, SHOW_IN_PLANNING - In Planung, SHOW_SQUAREDUP - Abgerechnet
    -->
    <STATUS>SHOW_ENABLED</STATUS>
</Show>
```
