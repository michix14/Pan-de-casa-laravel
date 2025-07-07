<script setup>
import AppLayout                    from '@/Layouts/AppLayout.vue';
import { ref, computed, onBeforeUnmount } from 'vue';
import { useForm, router }          from '@inertiajs/vue3';
import axios                        from 'axios';

/* ─────────── props ─────────── */
const props = defineProps({
  venta:   { type: Object, default: null },
  visitas: { type: Number, default: 0   }
});

/* ─── estados internos ─── */
const procesandoPago   = ref(false);
const qrGenerado       = ref(null);
const estadoPago       = ref('inicial');   // inicial | generando | esperando | completado | error
const mensajeError     = ref('');
const transactionId    = ref(null);
const nroPago          = ref(null);
const consultandoEstado= ref(false);

/* ─────────── form ─────────── */
const form = useForm({
  venta_id:    props.venta?.id ?? null,
  metodo_pago: 'qr',       // qr | tigo_money
  telefono:    '',
  ci_nit:      ''
});
const metodoPagoSeleccionado = computed(() => form.metodo_pago);

/* ─────────── generar QR / iniciar pago ─────────── */
const generarPago = async () => {
  if (!form.venta_id) {
    mensajeError.value = 'Debe seleccionar una venta válida';
    return;
  }

  procesandoPago.value = true;
  estadoPago.value     = 'generando';
  mensajeError.value   = '';

  try {
    const { data } = await axios.post(route('pagofacil.generar-qr'), form.data());

    if (data.success) {
      qrGenerado.value   = form.metodo_pago === 'qr' ? data.qr_image : null;
      transactionId.value= data.transaction_id;
      nroPago.value      = data.nro_pago;
      estadoPago.value   = 'esperando';

      iniciarConsultaEstado();
    } else {
      throw new Error(data.message ?? 'Error al generar el pago');
    }
  } catch (err) {
    console.error(err);
    estadoPago.value   = 'error';
    mensajeError.value = err.message ?? 'Error de conexión. Intente nuevamente.';
  } finally {
    procesandoPago.value = false;
  }
};

/* ─────────── polling de estado ─────────── */
let intervalo = null;

const iniciarConsultaEstado = () => {
  detenerConsultaEstado();
  intervalo = setInterval(consultarEstadoPago, 5000);        // cada 5 s
  setTimeout(detenerConsultaEstado, 10 * 60 * 1000);         // 10 min
};

const detenerConsultaEstado = () => {
  if (intervalo) { clearInterval(intervalo); intervalo = null; }
};

const consultarEstadoPago = async () => {
  if (consultandoEstado.value || !transactionId.value) return;
  consultandoEstado.value = true;

  try {
    const { data } = await axios.post(
      route('pagofacil.consultar-estado'),
      { transaction_id: transactionId.value }
    );

    if (data.success) {
      if (data.estado === 2) {           // completado
        estadoPago.value = 'completado';
        detenerConsultaEstado();
        setTimeout(() => {
          router.visit(route('pagofacil.return', { status: 'success', nro_pago: nroPago.value }));
        }, 1500);
      } else if (data.estado === 3) {    // rechazado
        estadoPago.value = 'error';
        mensajeError.value = 'El pago fue rechazado';
        detenerConsultaEstado();
      }
    }
  } catch (err) {
    console.error('consultarEstadoPago', err);
  } finally {
    consultandoEstado.value = false;
  }
};

onBeforeUnmount(detenerConsultaEstado);

/* ─────────── helpers ─────────── */
const reiniciarPago = () => {
  estadoPago.value   = 'inicial';
  qrGenerado.value   = null;
  mensajeError.value = '';
  transactionId.value= nroPago.value = null;
  form.reset();
  form.venta_id      = props.venta?.id ?? null;
};

const formatearMoneda = monto =>
  new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' })
      .format(typeof monto === 'number' ? monto : 0);
</script>
