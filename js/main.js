function Field() {
   this.fieldNode = arguments[0] ? arguments[0] : null;
}

Field.prototype.createHtml = function(data) {
   var html = '<td class="marktable-td-mark"><input name="mark[' + data.subtask + ']" value="' + (data.value ? data.value : '') + '" data-subtask="' + data.subtask + '" data-type="mark"></td>';
   return html;
}

Field.prototype.check = function() {
   
   if(this.fieldNode.data('type') == 'mark') {

      if(isNaN(parseFloat(this.fieldNode.val()))) {
         this.fieldNode.val('0');
      }
      
      var $col = $("#col-" + this.fieldNode.data('subtask'));
      if(this.fieldNode.val() > $col.data('max')) {
         this.fieldNode.addClass('marktable-td-error').attr('title','Значение выше максимального');
         return false;
      }
      else if(this.fieldNode.hasClass('marktable-td-error')) {
         this.fieldNode.removeClass('marktable-td-error').attr('title','');
      }
   }
   else {
      if(/[0-9]{1}-[0-9]{2}[a-zA-Z]{3}-[0-9]{1}/.test(this.fieldNode.val()) == false) {
         this.fieldNode.addClass('marktable-td-error').attr('title','Некорректный код');
         return false;
      }
      else if(this.fieldNode.hasClass('marktable-td-error')) {
         this.fieldNode.removeClass('marktable-td-error').attr('title','');
      }
   }
   return true;
}

function Row() {
   
   this.tableNode = null;
   this.id = 0;
   this.code = '';
   this.rowNode = arguments[0] ? arguments[0] : null;
   
}

Row.prototype.save = function() {
   
   var row = this;
   
   var data = {};
   row.rowNode.find('input').each(function(index){
      var $input = $(this);
      if($input.data('type') == 'code' || $input.data('type') == 'mark') {
         if($input.val() == '') {
            input.val() = '0';
         }
         var field = new Field($input);
         if(!field.check) {
            return;
         }
      }
      data[$input.attr('name')] = $input.val();
   });
   
   data.action = 'save_marks';
   
   row.rowNode.find('input,a').attr('disabled','disabled');
   
   $.getJSON('ajax.php',data,function(result){
      row.rowNode.find('input,a').removeAttr('disabled');
      if(!data.id && result.id) {
         row.rowNode.find('input[name="id"]').val(result.id);
         row.rowNode.data('id',result.id);
      }
      if(row.isLast()) {
         var new_row = new Row();
         row.rowNode.after(new_row.createHtml());
         row.rowNode.next().find('.marktable-code').focus();
      }
   })
}
Row.prototype.recount = function() {
   var sum = 0;
   this.rowNode.find('input').each(function(){
      if($(this).data('type') == 'mark') {
         sum += parseFloat($(this).val());
      }
   });
   this.rowNode.find('.marktable-td-sum').text(sum);
}
Row.prototype.createHtml = function(data) {
   
   if(data) {
      this.id = data.id ? data.id : 0;
      this.code = data.code ? data.code : '';
   }

   var html = '';
   
   html += '<tr data-id="' + this.id + '">';
   html += '<td class="marktable-td-code"><input name="id" type="hidden" value="' + this.id + '"><input name="code" value="' + this.code + '" data-type="code" class="marktable-code"></td>';
   
   var sum = 0;
   
   var i = 0;
   
   if(data && data.fields) {
      for(i=0;i<data.fields.length;++i) {
         var field = new Field();
         html += field.createHtml(data.fields[i]);
         sum += data.fields[i].value;
      }
   }
   else {
      $(".marktable-mark-col").each(function(){
         var field_data = {'subtask':$(this).data('id')};
         var field = new Field();
         html += field.createHtml(field_data);
      })
   }
   
   html += '<td class="marktable-td-sum">' + sum + '</td>';
   html += '<td class="marktable-td-submit"><a href="#">Сохранить</a></td>';
   html += '</tr>';
   return html;
}
Row.prototype.isLast = function() {
   return (this.rowNode.next().length == 0);
};

function Table(data) {
   this.tableNode = null;
   this.cols = [];
   this.rows = [];
   this.data = data;
   if(!data.node) {
      alert('Нужен элемент!');
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
      
      this.tableNode.delegate('input','blur',function(event){
        
         var field = new Field($(event.target));
         field.check();
         
         var row = new Row($(event.target).closest('tr'));
         row.recount();
      });
      
      this.tableNode.delegate('td.marktable-td-submit a', 'click', function(event){
         event.preventDefault();
         var row = new Row($(event.target).closest('tr'));
         row.save();
      });
      
      this.tableNode.delegate('tr', 'keypress', function(event){
         switch(event.which){
            // "ENTER"
            case 13:
               event.preventDefault();
               var row = new Row($(event.target).closest('tr'));
               row.recount();
               row.save();
            break;
            }
      });
   }
}
Table.prototype.createCols = function() {
   this.tableNode.append('<thead><tr id="table-header"></tr></thead>');
   var $header = $('#table-header');
   $("<th>Код</th>").appendTo($header);
   for(i=0;i<this.data.cols.length;++i) {
      $("<th id='col-" + this.data.cols[i].id + "' class='marktable-mark-col' data-id='" + this.data.cols[i].id +"' data-max='" + this.data.cols[i].max +"'>"+ this.data.cols[i].name +" (max " + this.data.cols[i].max + ")</th>").appendTo($header);
   }
}
Table.prototype.createRows = function() {
   var i = 0;
   for(i=0;i<this.data.rows.length;++i) {
      var row = new Row();
      this.tableNode.append(row.createHtml(this.data.rows[i]));
      
   }
}