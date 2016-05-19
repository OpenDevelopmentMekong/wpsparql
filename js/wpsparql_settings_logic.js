jQuery(document).ready(function() {

  var namespaces = [];
  initSettings();

  function removeElement(element,index) {
    console.log("deleting " + index);
    namespaces.splice(index,1);
    jQuery(element).remove();
    saveSettings();
  }

  function addElement(element,index){
    var prefix = jQuery(".namespace_element_input_prefix",element).val();
    var iri = jQuery(".namespace_element_input_iri",element).val();
    namespaces.push({"prefix":prefix,"iri":iri});
    addRow(namespaces.length);
    saveSettings();
  }

  function initSettings() {
    var supported_namespaces = jQuery("input[name=wpsparql_supported_namespaces]").val() || '[]';
    console.log(supported_namespaces);
    namespaces = JSON.parse(supported_namespaces);
    for (var index in namespaces) {
      addRow(index);
    }
    addRow(namespaces.length);
  }

  function addRow(index) {
    var elementRow = jQuery("#namespace_element_placeholder").clone();
    var newId = "namespace_element_" + index;

    elementRow.attr("id", newId);
    elementRow.show();

    var removeLink = jQuery("#remove_namespace", elementRow).click(function() {
      removeElement(elementRow,index);
      return false;
    });
    if (!namespaces[index]){
      removeLink.hide();
    }

    var addLink = jQuery("#add_namespace", elementRow).click(function() {
      jQuery(this).hide();
      removeLink.show();
      addElement(elementRow,index);
      return false;
    });
    if (namespaces[index]){
      addLink.hide();
    }

    var inputFieldPrefix = jQuery("#namespace_element_input_prefix", elementRow);
    inputFieldPrefix.attr("name", "namespace_element_input_prefix_" + index);
    inputFieldPrefix.attr("id", "namespace_element_input_prefix_" + index);

    var labelFieldPrefix = jQuery("#namespace_element_label_prefix", elementRow);
    labelFieldPrefix.attr("for", "namespace_element_input_prefix_" + index);

    var inputFieldIri = jQuery("#namespace_element_input_iri", elementRow);
    inputFieldIri.attr("name", "namespace_element_input_iri_" + index);
    inputFieldIri.attr("id", "namespace_element_input_iri_" + index);

    var labelFieldIri = jQuery("#namespace_element_label_iri", elementRow);
    labelFieldIri.attr("for", "namespace_element_input_iri_" + index);

    if (namespaces[index]) {
      inputFieldPrefix.val(namespaces[index].prefix);
      inputFieldIri.val(namespaces[index].iri);
    }

    jQuery("#supported_namespaces_list").append(elementRow);
  }

  function saveSettings() {
    jQuery("input[name=wpsparql_supported_namespaces]").val(JSON.stringify(namespaces));
  }
});
