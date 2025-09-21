function calcularPrecio() {
  const peso = parseFloat(document.getElementById("peso").value);
  const precio = document.getElementById("precio");
  const rango = document.getElementById("rango");
  const precioInput = document.getElementById("precio_envio");

  if (!peso || peso <= 0) {
    precio.textContent = "$0";
    rango.textContent = "Rango aplicado: -";
    precioInput.value = "";
    alert("Por favor ingrese un peso válido mayor a 0");
    return;
  }

  let valor = 0;
  let textoRango = "Sin rango aplicable";

  // Buscar el rango correspondiente
  for (let r of rangos) {
    if (peso >= parseFloat(r.peso_min) && peso <= parseFloat(r.peso_max)) {
      valor = peso * parseFloat(r.precio_kg);
      textoRango = r.rango_nombre;
      break;
    }
  }

  // Si no se encontró rango, usar el último (más alto)
  if (valor === 0 && rangos.length > 0) {
    const ultimoRango = rangos[rangos.length - 1];
    valor = peso * parseFloat(ultimoRango.precio_kg);
    textoRango = ultimoRango.rango_nombre + " (fuera de rango máximo)";
  }

  precio.textContent = "$" + valor.toLocaleString('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
  rango.textContent = "Rango aplicado: " + textoRango;
  precioInput.value = valor;
}

function cancelarCalculo() {
  document.getElementById("precio").textContent = "$0";
  document.getElementById("rango").textContent = "Rango aplicado: -";
  document.getElementById("precio_envio").value = "";
  document.getElementById("peso").value = "";
}

function validarFormulario() {
  if (document.getElementById("precio_envio").value === "") {
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
  const pesoInput = document.getElementById("peso");

  pesoInput.addEventListener('input', function() {
    if (pesoInput.value && parseFloat(pesoInput.value) > 0) {
      calcularPrecio();
    }
  });
});
