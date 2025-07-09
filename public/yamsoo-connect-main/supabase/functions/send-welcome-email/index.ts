
import { serve } from "https://deno.land/std@0.168.0/http/server.ts";
import { Resend } from "npm:resend@2.0.0";

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

interface EmailData {
  firstName: string;
  email: string;
}

serve(async (req) => {
  // Handle CORS preflight requests
  if (req.method === 'OPTIONS') {
    return new Response(null, { headers: corsHeaders });
  }

  // Validate content type
  const contentType = req.headers.get('content-type');
  if (!contentType || !contentType.includes('application/json')) {
    console.error('❌ Invalid content type:', contentType);
    return new Response(
      JSON.stringify({ error: 'Content-Type must be application/json' }),
      {
        status: 400,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
      }
    );
  }

  try {
    const data = await req.json();
    console.log('📥 Received request data:', data);

    // Validate required fields
    if (!data.firstName || !data.email) {
      console.error('❌ Missing required fields:', data);
      return new Response(
        JSON.stringify({ error: 'firstName and email are required' }),
        {
          status: 400,
          headers: { ...corsHeaders, 'Content-Type': 'application/json' },
        }
      );
    }

    const { firstName, email }: EmailData = data;
    console.log(`🚀 Starting to send welcome email to ${email} for ${firstName}`);

    const resendApiKey = Deno.env.get('RESEND_API_KEY');
    if (!resendApiKey) {
      console.error('❌ RESEND_API_KEY is not configured');
      throw new Error('RESEND_API_KEY is not configured');
    }

    console.log('🔑 Initializing Resend with API key:', resendApiKey.substring(0, 8) + '...');
    const resend = new Resend(resendApiKey);

    // En mode test, on envoie toujours à votre email personnel
    const testEmail = 'tifouriomar@gmail.com';
    console.log(`📧 Using test mode - redirecting email to ${testEmail}`);

    const { data: emailData, error: emailError } = await resend.emails.send({
      from: "YAMSOO <onboarding@resend.dev>",
      to: testEmail, // Utilisation de l'email de test
      subject: "Bienvenue sur YAMSOO !",
      html: `
        <h1>Bienvenue sur YAMSOO, ${firstName} !</h1>
        <p>Nous sommes ravis de vous accueillir sur YAMSOO, la plateforme qui connecte les familles.</p>
        <p>Pour commencer à utiliser YAMSOO, vous pouvez :</p>
        <ul>
          <li>Compléter votre profil</li>
          <li>Rechercher des membres de votre famille</li>
          <li>Créer des connexions familiales</li>
        </ul>
        <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
        <p>À bientôt sur YAMSOO !</p>
        <p><small>Note: Ceci est un email de test envoyé à ${testEmail}</small></p>
      `,
    });

    if (emailError) {
      console.error('❌ Error sending welcome email:', emailError);
      return new Response(
        JSON.stringify({ error: emailError.message }),
        {
          status: 500,
          headers: { ...corsHeaders, 'Content-Type': 'application/json' },
        }
      );
    }

    console.log('✅ Welcome email sent successfully:', emailData);
    return new Response(JSON.stringify({ success: true }), {
      headers: { ...corsHeaders, 'Content-Type': 'application/json' },
    });
  } catch (error) {
    console.error('❌ Error sending welcome email:', error);
    return new Response(
      JSON.stringify({ error: error.message }),
      {
        status: 500,
        headers: { ...corsHeaders, 'Content-Type': 'application/json' },
      }
    );
  }
});
