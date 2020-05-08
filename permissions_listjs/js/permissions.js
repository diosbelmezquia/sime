/**
 * @file
 * Provides JavaScript for permission_listjs
 */

(function ($, List) {
  Drupal.behaviors.permissionsListJS = {
    attach: function (context) {
      var $input = $('input.permissions-listjs', context).once('permissions-listjs');
      if ($input.length) {

        // Needed for List.js (apparently?)
        // http://listjs.com/examples/table/ (CodePen's HTML)
        $('table#permissions').find('tbody').addClass('list');

        var options = {
            valueNames: [ 'title' ],
            searchClass: 'permissions-listjs-search',
            fuzzySearch: {
              searchClass: "permissions-listjs-fuzzy-search",
              distance: 300
            }
        };
        var permissionsList = new List('user-admin-permissions', options);

        // Make sure hte permission list is full before submitting to prevent
        // permssions losing
        $('#user-admin-permissions').submit(function() {
          permissionsList.search('');
        });

      }
    }
  };
})(jQuery, List);
