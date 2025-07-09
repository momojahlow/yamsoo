
import { supabase } from "@/integrations/supabase/client";

export async function getProfiles() {
  try {
    const { data, error } = await supabase
      .from("profiles")
      .select("*");

    if (error) {
      console.error("Error fetching profiles:", error);
      return [];
    }

    return data;
  } catch (error) {
    console.error("Error fetching profiles:", error);
    return [];
  }
}
