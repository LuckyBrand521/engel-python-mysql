$.fn.dataTable.ext.order['date-time'] = function (settings, col) {
  return this.api().column(col, { order: 'index' }).nodes().map(function (td, i) {
      var val = $(td).text().trim();    // Get datetime string from <td>
      return moment(val, "DD.MM.YYYY").format("X"); 
  });
}

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const cat_id = urlParams.get('cat')

function format (d) {
  let html = '';
  html += '<table style="border: 1px solid black;"><tr><th>Preis</th><th>EK Preis</th><th>UVP Preis</th></tr>';
  for(let i = 0; i < d.history.length; i++) {
    html += `<tr><td>${d.history[i].price} €</td><td>${d.history[i].EK_price} €</td><td>${d.history[i].UVP_price} €</td></tr>`
  }
  html += '</table>';
  html += d.descrip;
  html += "<div style='display: flex; flex-direction: row;'>"
  const images = d.images.split(",");
  for(let i = 0; i < images.length; i++) {
    html += `<img src='${images[i]}' height='150px' />`
  }
  html += "</div>"
  return html;
}

const table = $('#table_id').DataTable({
  "paging":   true,
  "ordering": true,
  "info":     true,
  "searching": true,
  "ajax": "getdata.php?scraper=kwon",
  "lengthMenu": [ 20, 50, 100, 500, 1000, 5000, 10000 ],
  "columns": [
    {
      "className":      'details-control',
      "orderable":      false,
      "data":           null,
      "defaultContent": ''
    },
    { "data": "article_number" },
    { "data": "title", "render": function ( data, type, row, meta ) {
      if (row.url)
        return '<a href="'+row.url+'">'+data+'</a>';
      else
        return data;
    } },
    { "data": "price", "render": function (data) {
      return data ? data + " €" : "";
    } },
    { "data": "EK_price", "render": function (data) {
      return data ? data + " €" : "";
    } },
    { "data": "UVP_price", "render": function (data) {
      return data ? data + " €" : "";
    } },
    { "data": "selection1", "render": function (data, type, row, meta) {
      return data + (row.selection2 ? "," + row.selection2 : "");
    } },
    { "data": "categories" },
    { "data": "lastDate" },
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