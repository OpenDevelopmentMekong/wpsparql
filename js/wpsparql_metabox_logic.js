const DATASET_ID_ATTR = "wpsparql-dataset-id";
const DATASET_TITLE_ATTR = "wpsparql-dataset-title";
const DATASET_NAME_ATTR = "wpsparql-dataset-name";
const DATASET_GROUPS_ATTR = "wpsparql-dataset-groups";
const DATASET_EXTRAS_ATTR = "wpsparql-dataset-extras";
const DATASET_NUM_RESOURCES_ATTR = "wpsparql-dataset-num-resources";
const DATASET_ORG_ATTR = "wpsparql-dataset-org";
const CKAN_API_URL = "wpsparql-api-url"
const CKAN_BASE_URL = "wpsparql-base-url"

var field;
var datasetList;
var addButton;

var DEBUG = false;

var datasets = [];

jQuery( document ).ready(function() {
  if (DEBUG) console.log("wpsparql_metabox_logic.js document ready");

  //Init div elements
  field = jQuery('#wpsparql_related_datasets_add_field');
  datasetList = jQuery('#wpsparql_related_datasets_list');
  addButton = jQuery("#wpsparql_related_datasets_add_button");

  getFormValue();
  updateAndListDatasets();

  clearField();
  addButton.addClass("disabled");

  // Instantiate the Bloodhound suggestion engine
  var suggestions = new Bloodhound({
    datumTokenizer: function (datum) {
      return Bloodhound.tokenizers.whitespace(datum.value);
    },
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: field.attr(CKAN_API_URL) + '3/action/package_search?q=%QUERY',
      filter: function (json) {
        if (json.success){
          return jQuery.map(json.result.results, function (dataset) {
            return {
              id: dataset["id"],
              title: dataset["title"]+' ['+ listGroups(dataset["groups"])+']'+' ('+dataset["num_resources"]+')',
              name: dataset["name"],
              groups: JSON.stringify(dataset["groups"]),
              extras: JSON.stringify(dataset["extras"]),
              num_resources: dataset["num_resources"],
              owner_org: dataset["owner_org"]
            };
          });
        }
      }
    },limit:20
  });

  // Initialize the Bloodhound suggestion engine
  suggestions.initialize();

  // Instantiate the Typeahead UI
  jQuery('.typeahead').typeahead(null, {
    hint: true,
    minLength: 1,
    highlight: true,
    displayKey: 'title',
    source: suggestions.ttAdapter()
  }).on("typeahead:selected",function(event,item,dataset){
    if (DEBUG) {
      console.log(item["id"]);
      console.log(item["title"]);
      console.log(item["name"]);
      console.log(item["groups"]);
      console.log(item["extras"]);
      console.log(item["num_resources"]);
      console.log(item["owner_org"]);
    }

    jQuery(this).attr(DATASET_ID_ATTR,item["id"]);
    jQuery(this).attr(DATASET_TITLE_ATTR,escape(item["title"]));
    jQuery(this).attr(DATASET_NAME_ATTR,item["name"]);
    jQuery(this).attr(DATASET_GROUPS_ATTR,item["groups"]);
    jQuery(this).attr(DATASET_EXTRAS_ATTR,item["extras"]);
    jQuery(this).attr(DATASET_NUM_RESOURCES_ATTR,item["num_resources"]);
    jQuery(this).attr(DATASET_ORG_ATTR,item["owner_org"]);
    addButton.removeClass("disabled");
  });

  // TODO improve
  jQuery('.delete').on("click",function(){
    var dataset_id = jQuery(this).attr(DATASET_ID_ATTR);
    removeDatasetWithIdForEntry(dataset_id,this);
  });

});

function wpsparql_related_dataset_metabox_on_input(){
  if (DEBUG) console.log("wpsparql_related_dataset_metabox_on_input");

  addButton.addClass("disabled");
}

function wpsparql_related_dataset_metabox_add(){
  if (DEBUG) console.log("wpsparql_related_dataset_metabox_add");

  var dataset_id = field.attr(DATASET_ID_ATTR);
  var dataset_title = field.attr(DATASET_TITLE_ATTR);
  var dataset_url = field.attr(CKAN_BASE_URL) + "/dataset/" + field.attr(DATASET_NAME_ATTR);
  var dataset_groups = field.attr(DATASET_GROUPS_ATTR);
  var dataset_extras = field.attr(DATASET_EXTRAS_ATTR);
  var dataset_num_resources = field.attr(DATASET_NUM_RESOURCES_ATTR);
  var dataset_org = field.attr(DATASET_ORG_ATTR);

  var dataset = getDatasetWithId(dataset_id);
  if (dataset){
    clearField();
    return;
  }

  if (dataset_id){
   addDataset(true,dataset_id,dataset_title,dataset_url,dataset_groups,dataset_extras,dataset_num_resources,dataset_org);
   clearField();
  }

}

function updateAndListDatasets(){
  if (DEBUG) console.log("updateAndListDatasets");

  for (index in datasets){
    dataset_id = datasets[index]["dataset_id"];

    if (dataset_id){
      jQuery.ajax({
        url: field.attr(CKAN_BASE_URL) + "/api/3/action/package_show?id=" + dataset_id,
        context: document.body
      }).done(function(res) {
        if (DEBUG) console.log(res)
        if (res["success"])
          var dataset = res["result"];
          var dataset_id = dataset["id"];
          var dataset_title = dataset["title"]+' ['+ listGroups(dataset["groups"])+']'+' ('+dataset["num_resources"]+')';
          var dataset_url = field.attr(CKAN_BASE_URL) + "/dataset/" + dataset["id"];
          var dataset_groups = JSON.stringify(dataset["groups"]);
          var dataset_extras = JSON.stringify(dataset["extras"]);
          var dataset_n_resources = dataset["num_resources"];
          var dataset_org = dataset["owner_org"];
          addDataset(false,dataset_id,dataset_title,dataset_url,dataset_groups,dataset_extras,dataset_n_resources,dataset_org);
      });

    }

  }
}

function getFormValue(){
  var datasets_json = jQuery("#wpsparql_add_related_datasets_datasets").val();
  if (DEBUG) console.log("getFormValue "+ datasets_json);
  if (datasets_json) datasets = JSON.parse(datasets_json);
}

function setFormValue(){
  var datasets_json = JSON.stringify(datasets);
  if (DEBUG) console.log("setFormValue "+ datasets_json);
  jQuery("#wpsparql_add_related_datasets_datasets").val(datasets_json);
}

function addDataset(save_in_array,dataset_id,dataset_title,dataset_url,dataset_groups,dataset_extras,dataset_num_resources,dataset_org){

  if (save_in_array){
    datasets.push({"dataset_id": dataset_id, "dataset_title": dataset_title, "dataset_url": dataset_url, "dataset_groups": dataset_groups, "dataset_extras": dataset_extras, "dataset_num_resources": dataset_num_resources, "dataset_org": dataset_org});
    if (DEBUG) console.log("Added dataset with id: " + dataset_id + " datasets in array: "+ datasets.length);
    setFormValue();
  }

  var entry = jQuery('<p><a target="_blank" href='+dataset_url+'>'+unescape(dataset_title)+'</a>   </p>');
  var del = jQuery('<a class="delete error" '+DATASET_ID_ATTR+'='+dataset_id+' href="#">Delete</a>');
  // TODO improve
  jQuery(del).on("click",function(){
    var dataset_id = jQuery(this).attr(DATASET_ID_ATTR);
    removeDatasetWithIdForEntry(dataset_id,this);
  });
  entry.append(del);
  datasetList.append(entry);
}

//TODO optimize (using lodash)
function removeDatasetWithIdForEntry(id,entry){
  entry.parentNode.remove();  //use plain javascript here to get the parent
  var datasetIndex = getDatasetIndexWithId(id);
  if (datasetIndex){
    if (DEBUG) console.log("removing " + id + " from datasets");
    datasets.splice(datasetIndex,1);
    setFormValue();
    return;
  }

}

//TODO optimize (using lodash)
function getDatasetWithId(id){
  for (index in datasets){
    if (datasets[index]["dataset_id"] == id){
      return datasets[index];
    }
  }
  return null;
}

//TODO optimize (using lodash)
function getDatasetIndexWithId(id){
  for (index in datasets){
    if (datasets[index]["dataset_id"] == id){
      return index;
    }
  }
  return null;
}

function clearField(){
  field.val("");
}

function deserialize(s) {
  return unescape(s);
}

function serialize(s) {
  return escape(s);
}
