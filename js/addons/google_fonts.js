var webfonts = [];

function loadWebfont(font) {
  if (!($.inArray(font, webfonts) + 1)) {
    $('head').append(
      "<link href='https://fonts.googleapis.com/css2?family=" + font + "' rel='stylesheet' type='text/css'>"
    );
    webfonts.push(font);
    console.log('Loaded Font: ' + font);
  }
}
