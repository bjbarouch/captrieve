function e(e){return/[&<>"'\\]/.test(e)?e.replace(/&/g,"&#38;").replace(/</g,"&#60;").replace(/>/g,"&#62;").replace(/"/g,"&#34").replace(/'/g,"&#39;").replace(/\\/g,"&#92;"):e}export{e as safeHtml};
