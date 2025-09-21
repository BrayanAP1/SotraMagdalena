function calcularPrecio() {
  const ancho = parseFloat(document.getElementById("ancho").value) || 0;
  const alto = parseFloat(document.getElementById("alto").value) || 0;
  const largo = parseFloat(document.getElementById("largo").value) || 0;

  if (ancho <= 0 || alto <= 0 || largo <= 0) {
    alert("Por favor ingrese valores válidos para todas las dimensiones (mayores a 0).");
    return;
  }

  const volumen = ancho * alto * largo;
  let precioCalc = 0;
  let rango = "Sin rango aplicable";

  for (const r of rangos) {
    if (volumen >= parseFloat(r.minimo) && volumen <= parseFloat(r.maximo)) {
      precioCalc = volumen * parseFloat(r.precio_por_unidad);
      rango = r.nombre;
      break;
    }
  }

  if (precioCalc === 0 && rangos.length > 0) {
    const ultimoRango = rangos[rangos.length - 1];
    precioCalc = volumen * parseFloat(ultimoRango.precio_por_unidad);
    rango = ultimoRango.nombre + " (fuera de rango máximo)";
  }

  document.getElementById("precio").textContent = "$" + precioCalc.toFixed(2);
  document.getElementById("rango").textContent = "Rango aplicado: " + rango;
  document.getElementById("precioInput").value = precioCalc.toFixed(2);
  document.getElementById("rangoInput").value = rango;
}

function cancelarCalculo() {
  document.getElementById("precio").textContent = "$0";
  document.getElementById("rango").textContent = "Rango aplicado: -";
  document.getElementById("precioInput").value = "";
  document.getElementById("rangoInput").value = "";
  document.getElementById("ancho").value = "";
  document.getElementById("alto").value = "";
  document.getElementById("largo").value = "";
}

function prepararEnvio() {
  if (document.getElementById("precioInput").value === "") {
    alert("Por favor calcule el precio antes de guardar.");
    return false;
  }

  const requiredFields = document.querySelectorAll('input[required]');
  for (const field of requiredFields) {
    if (!field.value.trim()) {
      alert("Por favor complete todos los campos requeridos.");
      field.focus();
      return false;
    }
  }
  return true;
}

document.addEventListener('DOMContentLoaded', function() {
  const dimensionFields = ['ancho', 'alto', 'largo'];

  dimensionFields.forEach(field => {
    document.getElementById(field).addEventListener('input', function() {
      const allFilled = dimensionFields.every(f => {
        const value = document.getElementById(f).value;
        return value && parseFloat(value) > 0;
      });
      if (allFilled) calcularPrecio();
    });
  });
});
