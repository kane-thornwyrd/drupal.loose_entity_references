//=========================loose_entity_references=============================
/**
 * @file
 *   This is the main .js for the loose_entity_references module.
 */

/**
 * Main wrapper, to wait for the DOM to be ready plus maintaining a clean scope.
 *
 * @param $
 *   This is the variable embeding jQuery.
 * @param root
 *   This is the document root.
 * @param out
 *   This is for debug purpose only, a shorthand for console.log().
 * @param undef
 *   This is a handy method to have a real "undefined" since some browser seems
 *   to love messing the standards.
 */
(function($, root, out, undef){


  if( typeof root.getElementsByClass !== 'function'
    && typeof root.getElementsByTagName === 'function'){
    /**
     * Doing nearly the same thing as getElementsByTagName, except we are
     * searching for a specific class using the awesome Vanillia.js.
     *
     * @param classname
     *   The class of the elements you are searching the DOM for.
     *
     * @return Array
     *   the array of elements having the <classname>.
     */
    root.getElementsByClass = function(classname){
      var elems = root.getElementsByTagName('*'),i, out = [];
      for (i in elems) {
        if((" " + elems[i].className + " ").indexOf(" " + classname + " ") > -1) {
          out.push(elems[i]);
        }
      }
      return out;
    }
  }


  Drupal.behaviors.loose_entity_references = {
    // If you alter the module, there is a point you will have to modify this
    // too. Thank me for this convenience.
    selector : 'select-bundle-vertical-tab',

    attach : function(ctx, settings) {
      $('form#field-ui-field-edit-form div.select-bundle-vertical-tab div.vertical-tabs div.vertical-tabs-panes div.fieldset-wrapper div.vertical-tabs div.vertical-tabs-panes>fieldset.form-wrapper>legend').hide();
//      if(root.getElementsByClass(this.selector).length > 0){
//
//        var $elems_Field  = $('.'+this.selector+' .form-item-field .form-text'),
//        $elems_DM  = $('.'+this.selector+' .form-item-display-mode .form-text'),
//        selector = this.selector;
//        $elems_DM.change(function(e){
//          if(this.value.length > 0) {
//            $elems_DM.filter('[id!="' + this.id + '"]').val('');
//            out($(this).parentsUntil('.'+selector,'.fieldset-wrapper'));
//          }
//        });
//        $elems_Field.change(function(e){
//          if(this.value.length > 0) {
//            $elems_Field.filter('[id!="' + this.id + '"]').val('');
//            out($(this).parentsUntil('.'+selector,'.fieldset-wrapper'));
//          }
//        });
//        return true;
//      }else {
//        return false;
//      }
    },
  };

  return Drupal.behaviors.loose_entity_references;
})(jQuery, document, console.log);

//=========================loose_entity_references=END=========================
