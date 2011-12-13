function Field(data) {
   var id,max,name,type,row;
}
Field.prototype.isValid = function() {
}
Field.prototype.create = function() {
   
}

function Row(data) {
   this.table = data.table;
   this.id = data.id ? data.id : 0;
   this.code = data.code ? data.code : '';
   this.fields = [];
   if(data.fields) {
      
   }
}
Row.prototype.save = function() {
}
Row.prototype.recount = function() {
}
Row.prototype.create = function() {
}

function Table(data) {
   this.tableNode = null;
   this.cols = [];
   this.rows = [];
   this.data = data;
   if(!data.node) {
      alert('Нужен елемент!');
   }
   else {
      this.tableNode = $(this.data.node);
      if(!data.cols) {
         alert('Нет колонок');
      }
      else {
         this.createCols();
      }
      
      if(this.data.rows) {
         this.createRows();
      }
   }
}
Table.prototype.createCols = function() {
   this.tableNode.append('<thead><tr id="table-header"></tr></thead>');
   var $header = $('#table-header');
   for(i=0;i<this.data.cols.length;++i) {
      $("<th id='col-" + this.data.cols[i].id + "' data-id='" + this.data.cols[i].id +"' data-max='" + this.data.cols[i].max +"'>"+ this.data.cols[i].name +"</th>").appendTo($header);
   }
}
Table.prototype.createRows = function() {
   for(i=0;i<this.data.rows.length;++i) {
      var row_data = this.data.rows[i];
      row_data.node = n;
      var row = new Row(this.data.rows[i]);
      this.rows[i] = row;
   }
}

