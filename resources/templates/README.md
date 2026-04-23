You can upload the template from the Filament admin: **Settings → ID card template**.

Alternatively, place a default Word template file at:

`resources/templates/id-card-template.docx`

Use these placeholders in the DOCX:

- `${FIRST_NAME}`
- `${MIDDLE_INITIAL}`
- `${LAST_NAME}`
- `${FULL_NAME}`
- `${DESIGNATION}`
- `${OFFICE_NAME}`
- `${ID_NUMBER}`

Image placeholders:

- `${PROFILE_PICTURE}` for the uploaded employee photo
- `${QR_CODE}` for the generated QR code

Notes:

- Image placeholders must be inserted in Word as image placeholder text (plain `${...}`).
- Keep enough box size in the template where image placeholders are located.
- Generated DOCX files are saved under `storage/app/public/id_cards/`.
