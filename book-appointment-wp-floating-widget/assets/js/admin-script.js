(function($){
  $(function(){
    // General tab: animated messages
    $('#baw-add-message').on('click', function(){
      $('#baw-animated-messages').append('<div class="baw-row"><input type="text" name="animated_messages[]" value=""/> <button class="button baw-remove-row" type="button">&times;</button></div>');
    });
    $(document).on('click', '.baw-remove-row', function(){
      $(this).closest('.baw-row').remove();
    });

    // Form tab: fields list
    $('#baw-add-field').on('click', function(){
      const tpl = '<tr>'+
        '<td><input type="text" name="fields[][label]"/></td>'+
        '<td><input type="text" name="fields[][key]"/></td>'+
        '<td><select name="fields[][type]">'+
        ['text','textarea','number','email','select','checkbox','date','time','datetime-local','tel'].map(t=>`<option value="${t}">${t}</option>`).join('')+
        '</select></td>'+
        '<td><input type="text" name="fields[][placeholder]"/></td>'+
        '<td style="text-align:center"><input type="checkbox" name="fields[][required]" value="1"/></td>'+
        '<td style="text-align:center"><input type="checkbox" name="fields[][enabled]" value="1" checked/></td>'+
        '<td><button class="button baw-remove-field" type="button">&times;</button></td>'+
      '</tr>';
      $('#baw-fields-body').append(tpl);
    });
    $(document).on('click', '.baw-remove-field', function(){
      $(this).closest('tr').remove();
    });

    function submitForm($form) {
      const formData = $form.serializeArray();
      const fd = new FormData();
      formData.forEach(({name, value})=>fd.append(name, value));
      fd.append('action', 'baw_save_settings');
      $.ajax({
        url: BAW_ADMIN.ajax_url,
        method: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function(resp){
          if (resp && resp.success) {
            alert('Saved successfully');
          } else {
            alert('Save failed');
          }
        },
        error: function(){ alert('Save error'); }
      });
    }

    $('#baw-settings-form').on('submit', function(e){ e.preventDefault(); submitForm($(this)); });
    $('#baw-fields-form').on('submit', function(e){ e.preventDefault(); submitForm($(this)); });
  });
})(jQuery);


