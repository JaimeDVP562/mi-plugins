var canvas = new fabric.Canvas('c', {
  preserveObjectStacking: true,
});

// Example, upload SVG
// fabric.loadSVGFromURL('http://localhost/wordpress/wp-content/plugins/fabric-test/export/image.svg').then(({ objects }) => {
//   var obj = fabric.util.groupSVGElements(objects);
//
//   console.log( objects );
//
//   for( var i = 0; i < objects.length; i++ )
//     canvas.add(objects[i]);
// });

// Load background
fabric.loadSVGFromURL('http://localhost/wordpress/wp-content/plugins/fabric-test/assets/icons/Shapes/circle.svg').then(({ objects }) => {
  var obj = fabric.util.groupSVGElements(objects);

  // load the shape
  obj.clone().then(clone => {
    clone.set({
      left: 0,
      top: 0,
      fill: '#000000',
      strokeWidth: 0,
      strokeUniform: true,
      strokeLineJoin: 'round',
      stroke: '#ff0000',
    });

    clone.scaleToWidth(600);
    clone.scaleToHeight(600);

    canvas.add(clone);
  })
});

// Load Icon
fabric.loadSVGFromURL('http://localhost/wordpress/wp-content/plugins/fabric-test/assets/icons/System/star.svg').then(({ objects }) => {
  var obj = fabric.util.groupSVGElements(objects);

  // load the shape
  obj.clone().then(clone => {
    clone.set({
      left: 150,
      top: 150,
      fill: '#ffffff',
      strokeWidth: 0,
      strokeUniform: true,
      strokeLineJoin: 'round',
      stroke: '#ff0000',
    });

    clone.scaleToWidth(300);
    clone.scaleToHeight(300);

    canvas.add(clone);
  })
});


//canvas.setActiveObject(rect);
canvas.renderAll();


var fillColorInput = document.getElementById('fillColor');
var fillColor2Input = document.getElementById('fillColor2');
var gradientAngleInput = document.getElementById('gradientAngle');
var gradientTypeInput = document.getElementById('gradientType');
var strokeWidthInput = document.getElementById('strokeWidth');
var strokeTypeInput = document.getElementById('strokeType');
var strokeColorInput = document.getElementById('strokeColor');

fillColorInput.addEventListener('input', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  obj.set('fill', fillColorInput.value);
  canvas.renderAll();
});

fillColor2Input.addEventListener('input', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  updateGradient();
});

gradientTypeInput.addEventListener('change', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  updateGradient();
});

gradientAngleInput.addEventListener('input', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  updateGradient();
});

function updateGradient() {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  var angle = gradientAngleInput.value;

  if (gradientTypeInput.value === 'radial') {
    var coords = {
      r1: obj.height / 2 + obj.width / 4,
      r2: obj.width * .05,
      x1: obj.width / 2,
      y1: obj.height / 2,
      x2: obj.width / 2,
      y2: obj.height / 2,
    };
  } else {
    var start = angle2rect(angle, obj.width, obj.height);
    var end = {
      x: obj.width - start.x,
      y: obj.height - start.y
    }

    var coords = {
      x1: start.x,
      y1: start.y,
      x2: end.x,
      y2: end.y,
    };
  }


  var gradient = new fabric.Gradient({
    type: gradientTypeInput.value,
    gradientUnits: 'pixels',
    coords: coords,
    colorStops: [
      { offset: 0, color: fillColorInput.value },
      { offset: 1, color: fillColor2Input.value }
    ]
  });

  obj.set('fill', gradient);

  canvas.renderAll();
}

/* convert angle to rectangle perimeter coordinates */
function angle2rect(angle, sx, sy) {
  while (angle < 0) angle += 360; angle %= 360;

  var a = sy, b = a + sx, c = b + sy, // 3 first corners
    p = (sx + sy) * 2, // perimeter
    rp = p * 0.00277, // ratio between perimeter & 360
    pp = Math.round(((angle * rp) + (sy >> 1)) % p); // angle position on perimeter

  if (pp <= a) return { x: 0, y: sy - pp };
  if (pp <= b) return { y: 0, x: pp - a };
  if (pp <= c) return { x: sx, y: pp - b };
  return { y: sy, x: sx - (pp - c) };
}

strokeWidthInput.addEventListener('input', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  var value = parseInt(strokeWidthInput.value);

  if (isNaN(value)) {
    value = 0;
  }

  obj.set('strokeWidth', value);
  canvas.renderAll();
});

strokeTypeInput.addEventListener('change', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  obj.set('strokeLineJoin', strokeTypeInput.value);
  canvas.renderAll();
});

strokeColorInput.addEventListener('input', function () {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  obj.set('stroke', strokeColorInput.value);
  canvas.renderAll();
});

canvas.on('selection:created', updateFields);
canvas.on('selection:updated', updateFields);

function updateFields() {
  var obj = canvas.getActiveObject();
  if (!obj) return;

  console.log(obj.fill);
  console.log(typeof obj.fill);
  console.log(obj);

  if (typeof obj.fill == 'object') {
    // Gradient
    fillColorInput.value = '#' + new fabric.Color(obj.fill.colorStops[0].color).toHex();
    fillColor2Input.value = '#' + new fabric.Color(obj.fill.colorStops[1].color).toHex();
    gradientTypeInput.value = obj.fill.type;
  } else {
    // Single color
    fillColorInput.value = '#' + new fabric.Color(obj.fill).toHex();
    fillColor2Input.value = fillColorInput.value;
    gradientTypeInput.value = 'linear';
  }

  strokeWidthInput.value = obj.strokeWidth;
  strokeColorInput.value = obj.stroke;
}


canvas.on('selection:cleared', function () {

});

var downloadPNGInput = document.getElementById('download-png');

downloadPNGInput.addEventListener('click', function () {
  download_file(canvas.toDataURL('png'), 'image.png');
});

var downloadSVGInput = document.getElementById('download-svg');

downloadSVGInput.addEventListener('click', function () {
  download_file("data:image/svg+xml;charset=utf-8," + encodeURIComponent(canvas.toSVG()), 'image.svg');
});

function download_file(url, filename) {

  var a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

}
