$.fn.dataTable.ext.order['date-time'] = function (settings, col) {
  return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
      var val = $(td).text().trim();    // Get datetime string from <td>
      return moment(val, "DD.MM.YYYY").format("X"); 
  });
}

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const cat_id = urlParams.get('cat')

const stock = {
  "delivery--status-more-is-coming": "Lagerbestand <= 10",
  "delivery--status-available": "Verfügbar",
  "delivery--status-not-available": "In Produktion, wird nachgeliefert",
  "unknown": "Nicht Verfügbar"
}

const delivery = {
  "delivery--status-available": "3 Tage",
  "delivery--status-more-is-coming": "10 Tage",
  "delivery--status-not-available": "20 Tage",
  "unknown": ""
}

const color = {
  "delivery--status-more-is-coming": "orange",
  "delivery--status-available": "green",
  "delivery--status-not-available": "red",
  "unknown": "black"
}

function format(d) {
  let html = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;"><thead><tr><td>Variante</td><td>Größe</td><td>Preis</td><td>Lagerbestand</td><td>Lieferung</td><td>SKU Nummer</td><td>EAN</td><td>Gewicht</td><td>UVP</td><td>Qualität</td><td>Zertifizierung</td></tr></thead><tbody>'
  for(let i = 0; i < d.sizes.length; i++) {
    const row = d.sizes[i]
    if(d.article_num.includes("-")) {
      d.article_num = d.article_num.split("-")[0];
    }
    const sku = d.article_num + "-" + row.variant.split("-")[0] + "-" + row.size.replace("/", "")
    html += `<tr>
    <td>${row.variant}</td>
    <td>${row.size}</td>
    <td>${row.price}</td>
    <td>${stock[row.stock]}</td>
    <td style='color:${color[row.stock]}'>${delivery[row.stock]}</td>
    <td>${sku}</td>
    <td>${row.EAN}</td>
    <td>${row.weight}</td>
    <td>${row.UVP}</td>
    <td>${row.quality}</td>
    <td>${row.certification}</td>
    </tr>`;
  }
  html += "</tbody></table>"
  html += "<div style='display: flex; flex-direction: row;'>"
  for(let i = 0; i < d.images.length; i++) {
    const image = d.images[i].img_url
    html += `<img src='${image}' height='150px' />`
  }
  html += "</div>"
  html += d.descrip;
  return html;
}

const table = $('#table_id').DataTable({
  "paging":   true,
  "ordering": true,
  "info":     true,
  "searching": true,
  "ajax": "getdata.php?scraper=engel",
  "lengthMenu": [ 20, 50, 100, 500, 1000, 5000, 10000 ],
  "columns": [
    {
      "className":      'details-control',
      "orderable":      false,
      "data":           null,
      "defaultContent": ''
    },
    { "data": "article_num" },
    { "data": "title", "render": function ( data, type, row, meta ) {
      if (row.url)
        return '<a href="'+row.url+'">'+data+'</a>';
      else
        return data;
    } },
  ],
  "columnDefs": [

  ],
  "order": [[ 0, "asc" ]],
});

$('tbody').on('click', 'td.details-control', function () {
  var tr = $(this).closest('tr');
  var row = table.row( tr );

  if ( row.child.isShown() ) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
  }
  else {
      // Open this row
      row.child( format(row.data()) ).show();
      tr.addClass('shown');
  }
} );