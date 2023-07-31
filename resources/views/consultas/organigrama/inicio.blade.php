

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link href="{{asset('css/app.css')}}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <link href="{{asset('css/styles.css')}}" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css">
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  @php $empresa = 'EMPRESA '. Session::get('empresa')['razon_social'] @endphp
  <title>Organigrama | {{$empresa}} </title>
</head>
<body>
  @include('includes.spinner')
  <div class="container custom-view">
    <div class="row">
      <div class="col-md-1 col-sm-12"></div>
      <div class="col-md-1 col-sm-12 button-back-center ">
        <div class="mt-4">
          <a href="{{ route('bandeja') }}" data-toggle="tooltip" title="Regresar" ref="Bandeja de notificaciones">
            @include('includes.back')
          </a>
        </div>
        <br>
      </div>
      <div class="col-md-8 col-sm-12">
        <div style="display: flex; justify-content:center;">
          <img src="{{asset('img\header\norma/icono-diagrama.png')}}" alt="Periodo de implementación" class="w-px-35 text-center" style="margin-left: -2%;">
        </div>
        <div style="display: flex; justify-content:center;" class="mt-2" >
          <label class="custom-title mr-5 text-center">{{$empresa}}</label>
        </div>
        <div style="display: flex; justify-content:center; ">
            <label class=" text-center top-line-black mr-5 w-20">Organigrama</label>
        </div>
      </div>
      <div class="col-md-2"></div>
    </div>
    <div class="row">
      <div class="col-md-12" >

        @if(session()->has('success'))
          <div class="row">
              <div class="alert alert-success" style="width: 100%;" align="center">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <strong>Notificación: </strong>
                  {{ session()->get('success') }}
              </div>
          </div>
        @elseif(session()->has('danger'))
          <div class="row">
              <div class="alert alert-danger" style="width: 100%;" align="center">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                  <strong>Notificación: </strong>
                  {{ session()->get('danger') }}
              </div>
          </div>
        @endif
          <div id="sample">
            <div id="myDiagramDiv" class="content-org"></div>
            <br>
            <div class="d-flex justify-content-center">
              <button id="zoomToFit" class="button-style-cancel">Acercar organigrama</button> 
              <button id="centerRoot" class="button-style ml-1">Centrar organigrama</button>
            </div>
            <div>
            <div id="myInspector"></div>
            </div>
          </div> 
        </div>
      </div>
    </div>
  </div>
    <!-- * * * * * * * * * * * * * -->
    <!-- Start of GoJS sample code -->
    <script id="code">
      var mensaje = '@php echo $mensaje @endphp';
      if(mensaje){
  
        setTimeout(function(){   $('#spinner').addClass('ocultar') }, 1000);
          
      }else{
            
        alertify.error("Error al generar el organigrama comunicate con tu administrador");
      }
  
      function init() {
        var $ = go.GraphObject.make;  // for conciseness in defining templates
  
        myDiagram =
          $(go.Diagram, "myDiagramDiv", // must be the ID or reference to div
            {
              /*maxSelectionCount: 1, // users can select only one part at a time
              validCycle: go.Diagram.CycleDestinationTree, // make sure users can only create trees
              "clickCreatingTool.archetypeNodeData": { // allow double-click in background to create a new node
                name: "(new person)",
                title: "",
                comments: ""
              },
              "clickCreatingTool.insertPart": function(loc) {  // scroll to the new node
                var node = go.ClickCreatingTool.prototype.insertPart.call(this, loc);
                if (node !== null) {
                  this.diagram.select(node);
                  this.diagram.commandHandler.scrollToPart(node);
                  this.diagram.commandHandler.editTextBlock(node.findObject("NAMETB"));
                }
                return node;
              },*/
              layout:
                $(go.TreeLayout,
                  {
                    treeStyle: go.TreeLayout.StyleLastParents,
                    arrangement: go.TreeLayout.ArrangementHorizontal,
                    // properties for most of the tree:
                    angle: 90,
                    layerSpacing: 35,
                    // properties for the "last parents":
                    alternateAngle: 90,
                    alternateLayerSpacing: 35,
                    alternateAlignment: go.TreeLayout.AlignmentBus,
                    alternateNodeSpacing: 20
                  }),
              "undoManager.isEnabled": false // enable undo & redo
            });
  
        // when the document is modified, add a "*" to the title and enable the "Save" button
        /*myDiagram.addDiagramListener("Modified", function(e) {
          var button = document.getElementById("SaveButton");
          if (button) button.disabled = !myDiagram.isModified;
          var idx = document.title.indexOf("*");
          if (myDiagram.isModified) {
            if (idx < 0) document.title += "*";
          } else {
            if (idx >= 0) document.title = document.title.substr(0, idx);
          }
        });*/
  
        // manage boss info manually when a node or link is deleted from the diagram
        /*myDiagram.addDiagramListener("SelectionDeleting", function(e) {
          var part = e.subject.first(); // e.subject is the myDiagram.selection collection,
          // so we'll get the first since we know we only have one selection
          myDiagram.startTransaction("clear boss");
          if (part instanceof go.Node) {
            var it = part.findTreeChildrenNodes(); // find all child nodes
            while (it.next()) { // now iterate through them and clear out the boss information
              var child = it.value;
              var bossText = child.findObject("boss"); // since the boss TextBlock is named, we can access it by name
              if (bossText === null) return;
              bossText.text = "";
            }
          } else if (part instanceof go.Link) {
            var child = part.toNode;
            var bossText = child.findObject("boss"); // since the boss TextBlock is named, we can access it by name
            if (bossText === null) return;
            bossText.text = "";
          }
          myDiagram.commitTransaction("clear boss");
        });*/
  
        var levelColors = ["#fbba00"];
          /*var levelColors = ["#f0c018", "#2672EC", "#8C0095", "#5133AB",
          "#008299", "#D24726", "#008A00", "#094AB2"];*/
  
        // override TreeLayout.commitNodes to also modify the background brush based on the tree depth level
        myDiagram.layout.commitNodes = function() {
          go.TreeLayout.prototype.commitNodes.call(myDiagram.layout);  // do the standard behavior
          // then go through all of the vertexes and set their corresponding node's Shape.fill
          // to a brush dependent on the TreeVertex.level value
          myDiagram.layout.network.vertexes.each(function(v) {
            if (v.node) {
              var level = v.level % (levelColors.length);
              var color = levelColors[level];
              var shape = v.node.findObject("SHAPE");
              if (shape) shape.stroke = $(go.Brush, "Linear", { 0: color, 1: go.Brush.lightenBy(color, 0.05), start: go.Spot.Left, end: go.Spot.Right });
            }
          });
        };
  
        // when a node is double-clicked, add a child to it
        /*function nodeDoubleClick(e, obj) {
          var clicked = obj.part;
          if (clicked !== null) {
            var thisemp = clicked.data;
            myDiagram.startTransaction("add employee");
            var newemp = {
              name: "(new person)",
              title: "",
              comments: "",
              parent: thisemp.key
            };
            myDiagram.model.addNodeData(newemp);
            myDiagram.commitTransaction("add employee");
          }
        }*/
  
        // this is used to determine feedback during drags
        //function mayWorkFor(node1, node2) {
          /*if (!(node1 instanceof go.Node)) return false;  // must be a Node
          if (node1 === node2) return false;  // cannot work for yourself
          if (node2.isInTreeOf(node1)) return false;  // cannot work for someone who works for you
          return true;*/
        //}
  
        // This function provides a common style for most of the TextBlocks.
        // Some of these values may be overridden in a particular TextBlock.
        function textStyle() {
          return { font: "9pt  Segoe UI,sans-serif", stroke: "black" };
        }
  
        // This converter is used by the Picture.
        function findHeadShot(img) {
          
          if (img.length > 0 ){
            var i =img.split(/(\\|\/)/g).pop();
            i = i.split('.');
            if(i[0]!=='file_fotografia'){ 
              return img;
            }else{
               return "{{asset('img/avatar.png')}}"
            }
          }
        
        }
  
        // define the Node template
        myDiagram.nodeTemplate =
          $(go.Node, "Auto",
            //{ doubleClick: nodedoubleClick },
            { // handle dragging a Node onto a Node to (maybe) change the reporting relationship
              /*mouseDragEnter: function(e, node, prev) {
                var diagram = node.diagram;
                var selnode = diagram.selection.first();
                if (!mayWorkFor(selnode, node)) return;
                var shape = node.findObject("SHAPE");
                if (shape) {
                  shape._prevFill = shape.fill;  // remember the original brush
                  shape.fill = "darkred";
                }
              },*/
              /*mouseDragLeave: function(e, node, next) {
                var shape = node.findObject("SHAPE");
                if (shape && shape._prevFill) {
                  shape.fill = shape._prevFill;  // restore the original brush
                }
              },*/
              /*mouseDrop: function(e, node) {
                var diagram = node.diagram;
                var selnode = diagram.selection.first();  // assume just one Node in selection
                if (mayWorkFor(selnode, node)) {
                  // find any existing link into the selected node
                  var link = selnode.findTreeParentLink();
                  if (link !== null) {  // reconnect any existing link
                    link.fromNode = node;
                  } else {  // else create a new link
                    diagram.toolManager.linkingTool.insertLink(node, node.port, selnode, selnode.port);
                  }
                }
              }*/
            },
         
            $(go.Shape, "Rectangle",
              {
                name: "SHAPE", fill: "#fff", stroke: 'white', strokeWidth: 3.5,
                // set the port properties:
                portId: "", fromLinkable: true, toLinkable: true, cursor: "pointer"
              }),
            $(go.Panel, "Horizontal",
              $(go.Picture,
                {
                  name: "Picture",
                  desiredSize: new go.Size(100, 100),
                  margin: 1.5,
                },
                new go.Binding("source", "img", findHeadShot)),
              // define the panel where the text will appear
              $(go.Panel, "Table",
                {
                  minSize: new go.Size(200, NaN),
                  maxSize: new go.Size(200, NaN),
                  margin: new go.Margin(6, 10, 0, 6),
                  defaultAlignment: go.Spot.Left
                },
                $(go.RowColumnDefinition, { column: 2, width: 4 }),
                $(go.TextBlock, textStyle(),  // the name
                  {
                    row: 0, column: 0, columnSpan: 5,
                    font: "12pt Segoe UI,sans-serif",
                    editable: false, isMultiline: false,
                    minSize: new go.Size(10, 16)
                  },
                  new go.Binding("text", "name").makeTwoWay()),
                $(go.TextBlock, "Dpt: ", textStyle(),
                  { row: 1, column: 0 }),
                $(go.TextBlock, textStyle(),
                  {
                    row: 1, column: 1, columnSpan: 3,
                    editable: false, isMultiline: false,
                    minSize: new go.Size(10, 14),
                    margin: new go.Margin(0, 0, 0, 3)
                  },
                  new go.Binding("text", "title").makeTwoWay()),
  
                $(go.TextBlock, textStyle(),
                  { name: "post", row: 2, columnSpan: 3, }, // we include a name so we can access this TextBlock when deleting Nodes/Links
                  new go.Binding("text", "post", function(v) { return "Puesto: " + v; })),
                $(go.TextBlock, textStyle(),  // the comments
                  {
                    row: 3, column: 0, columnSpan: 5,
                    font: "italic 9pt sans-serif",
                    wrap: go.TextBlock.WrapFit,
                    editable: false,  // by default newlines are allowed
                    minSize: new go.Size(10, 14)
                  },
                  new go.Binding("text", "comments").makeTwoWay())
              )  // end Table Panel
            ) // end Horizontal Panel
          );  // end Node
  
        
  
        // define the Link template
        myDiagram.linkTemplate =
          $(go.Link, go.Link.Orthogonal,
            { corner: 5, relinkableFrom: true, relinkableTo: true },
            $(go.Shape, { strokeWidth: 1.5, stroke: "#000" }));  // the link shape
  
        // read in the JSON-format data from the "mySavedModel" element
        load();
  
  
        // support editing the properties of the selected person in HTML
        /*if (window.Inspector) myInspector = new Inspector("myInspector", myDiagram,
          {
            properties: {
              "key": { readOnly: true },
              "comments": {}
            }
          });*/
  
        // Setup zoom to fit button
        document.getElementById('zoomToFit').addEventListener('click', function() {
          myDiagram.commandHandler.zoomToFit();
        });
  
        document.getElementById('centerRoot').addEventListener('click', function() {
          myDiagram.scale = 1;
          //myDiagram.commandHandler.scrollToPart(myDiagram.findNodeForKey(1));
        });
  
      } // end init
  
      // Show the diagram's model in JSON format
      /*function save() {
        document.getElementById("mySavedModel").value = myDiagram.model.toJson();
        myDiagram.isModified = false;
      }*/
      function load() {
  
        var collection ='@php echo json_encode($collection) @endphp';
     
        var collectionJSON ={
          "class": "go.TreeModel",
          "nodeDataArray":JSON.parse(collection)
        } 
        myDiagram.model = go.Model.fromJson(JSON.stringify(collectionJSON));
        // make sure new data keys are unique positive integers
        var lastkey = 1;
        myDiagram.model.makeUniqueKeyFunction = function(model, data) {
          var k = data.key || lastkey;
          while (model.findNodeDataForKey(k)) k++;
          data.key = lastkey = k;
          return k;
        };
      }
      window.addEventListener('DOMContentLoaded', init);
 
    </script>

  <!--<script src="https://cdn.jsdelivr.net/npm/gojs@2.1/release/go.js"></script>-->
  <script src='{{asset("js/orgchart/go.js")}}'></script>
  <!--<script src='{ {asset("public/js/orgchart/require.js")}}'></script>-->
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script src="{{asset('js/parsley/parsley.min.js')}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- Cambiar idioma de parsley -->
  <script src="{{asset('js/parsley/i18n/es.js')}}"></script>
  <script>

    $(function(){

      $('.select-puesto').select2();
      let canvas = document.querySelector("canvas");
      canvas.setAttribute("class", "div-org");
      
    });

  $("#spinner").toggle();
  </script>
</body>
</html>