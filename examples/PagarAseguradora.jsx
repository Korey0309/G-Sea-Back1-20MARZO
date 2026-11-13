/**
 * Copia a: src/components/PagarAseguradora.jsx
 * Y en App.jsx agrega:
 *
 *   import PagarAseguradora from './components/PagarAseguradora';
 *   <Route path="/pagar/:slug" element={<PagarAseguradora />} />
 *
 * (ruta pública, sin ProtectedRoute)
 */
import { useEffect } from 'react';
import { useParams } from 'react-router-dom';

const URLS = {
  qualitas: 'https://www.qualitas.com.mx/web/qmx/pago-de-poliza',
  axa: 'https://cajaaxa.mitec.com.mx/cua/inicio.do?method=loginAgente&perfil=cliente',
  hdi: 'https://portalpagos.hdi.com.mx/identificaPoliza',
  chubb: 'https://clientes-seguros.mx.chubb.com/pago-poliza',
  gnp: 'https://www.gnp.com.mx/pagar-en-linea-mi-seguro-gnp',
  mapfre: 'https://mapfrepayonline.mapfre.com.mx/landing',
  allianz: 'https://www.allianz.com.mx/servicios/tramites/pago-en-linea.html',
  inbursa: 'https://asesores.inbursa.com/PortalAsesores/toEnvia.do?Acceso=1',
  monterrey: 'https://inv.mx/pagar',
  general: 'https://inv.mx/pagar',
};

export default function PagarAseguradora() {
  const { slug } = useParams();

  useEffect(() => {
    const key = String(slug || 'general').toLowerCase();
    window.location.replace(URLS[key] || URLS.general);
  }, [slug]);

  return (
    <p style={{ fontFamily: 'sans-serif', padding: 24 }}>
      Redirigiendo al portal de pago…
    </p>
  );
}
