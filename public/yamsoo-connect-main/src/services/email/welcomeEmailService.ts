
import { supabase } from "@/integrations/supabase/client";

export const sendWelcomeEmail = async (firstName: string, email: string) => {
  try {
    console.log("📧 Attempting to send welcome email...");
    const { error: emailError } = await supabase.functions.invoke('send-welcome-email', {
      body: { firstName, email },
    });

    if (emailError) {
      console.error('❌ Error sending welcome email:', emailError);
    } else {
      console.log('✉️ Welcome email sent successfully');
    }
  } catch (emailError) {
    console.error('❌ Failed to send welcome email:', emailError);
  }
};
