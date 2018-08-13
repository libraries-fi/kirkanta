# Changes in Kirkanta
1. Move coordinates field to Address entity.
2. Replace 'phone number' entities with 'contact detail' entities.
3. Service data reform.
4. Split coordinates to two columns in database.
5. Populate Organisation.consortium even for regular libraries (makes building API queries easier)
6. Maybe lock the consortium field on Organisation form for regular libraries?
7. Organisation.web_library is dropped.
8. Why does only Person::$email have a visibility switch?
