
import { useQuery } from "@tanstack/react-query";
import { supabase } from "@/integrations/supabase/client";
import { useToast } from "@/hooks/use-toast";
import { useIsMobile } from "@/hooks/use-mobile";
import { MobileNavBar } from "@/components/mobile/MobileNavBar";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AppSidebar } from "@/components/app/Sidebar";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

type UserRole = 'user' | 'admin' | 'super_admin';

type UserWithRole = {
  id: string;
  email: string;
  role: UserRole;
  profile: {
    first_name: string;
    last_name: string;
    avatar_url: string | null;
    gender: string;
  };
};

const Admin = () => {
  const isMobile = useIsMobile();
  const { toast } = useToast();

  const { data: usersWithRoles, isLoading: isLoadingUsers } = useQuery({
    queryKey: ['users'],
    queryFn: async () => {
      // Récupérer tous les profils
      const { data: profiles, error: profilesError } = await supabase
        .from('profiles')
        .select('*');

      if (profilesError) throw profilesError;

      // Récupérer tous les rôles d'utilisateur
      const { data: userRoles, error: rolesError } = await supabase
        .from('user_roles')
        .select('*');

      if (rolesError) throw rolesError;

      // Combiner les données
      const usersWithRoles = profiles.map((profile: any) => {
        const userRole = userRoles.find((role: any) => role.user_id === profile.id);
        return {
          id: profile.id,
          email: profile.email,
          role: userRole?.role || 'user',
          profile: {
            first_name: profile.first_name,
            last_name: profile.last_name,
            avatar_url: profile.avatar_url,
            gender: profile.gender
          }
        };
      });

      return usersWithRoles;
    }
  });

  const totalUsers = usersWithRoles?.length || 0;
  const maleUsers = usersWithRoles?.filter(user => user.profile.gender === 'M').length || 0;
  const femaleUsers = usersWithRoles?.filter(user => user.profile.gender === 'F').length || 0;
  
  const handleRoleChange = async (userId: string, newRole: UserRole) => {
    try {
      const { error } = await supabase
        .from('user_roles')
        .upsert({ 
          user_id: userId, 
          role: newRole 
        }, {
          onConflict: 'user_id'
        });

      if (error) throw error;

      toast({
        title: "Rôle mis à jour",
        description: "Le rôle de l'utilisateur a été modifié avec succès.",
      });
    } catch (error) {
      toast({
        title: "Erreur",
        description: "Impossible de mettre à jour le rôle de l'utilisateur.",
        variant: "destructive",
      });
    }
  };

  return (
    <SidebarProvider>
      <div className="min-h-screen flex flex-col md:flex-row w-full bg-background">
        <AppSidebar />
        <main className="flex-1 p-4 md:p-8 md:ml-16 pb-20 md:pb-8">
          <div className="space-y-4">
            <h1 className="text-2xl md:text-3xl font-bold">Tableau de bord administrateur</h1>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Card>
                <CardHeader>
                  <CardTitle>Total Utilisateurs</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-3xl font-bold">{totalUsers}</p>
                </CardContent>
              </Card>
              
              <Card>
                <CardHeader>
                  <CardTitle>Hommes</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-3xl font-bold">{maleUsers}</p>
                  <p className="text-sm text-muted-foreground">
                    {((maleUsers / totalUsers) * 100).toFixed(1)}%
                  </p>
                </CardContent>
              </Card>
              
              <Card>
                <CardHeader>
                  <CardTitle>Femmes</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-3xl font-bold">{femaleUsers}</p>
                  <p className="text-sm text-muted-foreground">
                    {((femaleUsers / totalUsers) * 100).toFixed(1)}%
                  </p>
                </CardContent>
              </Card>
            </div>

            <div className="rounded-lg border bg-card">
              <div className="overflow-x-auto">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Utilisateur</TableHead>
                      <TableHead className="hidden md:table-cell">Email</TableHead>
                      <TableHead>Rôle</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {usersWithRoles?.map((user) => (
                      <TableRow key={user.id}>
                        <TableCell className="flex items-center gap-2">
                          <Avatar className="h-8 w-8">
                            <AvatarImage src={user.profile.avatar_url || ''} />
                            <AvatarFallback>
                              {user.profile.first_name[0]}
                              {user.profile.last_name[0]}
                            </AvatarFallback>
                          </Avatar>
                          <div>
                            <p className="font-medium">
                              {user.profile.first_name} {user.profile.last_name}
                            </p>
                            <p className="text-sm text-muted-foreground md:hidden">{user.email}</p>
                          </div>
                        </TableCell>
                        <TableCell className="hidden md:table-cell">{user.email}</TableCell>
                        <TableCell>
                          <Select
                            defaultValue={user.role}
                            onValueChange={(value: UserRole) => handleRoleChange(user.id, value)}
                          >
                            <SelectTrigger className="w-32">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              <SelectItem value="user">Utilisateur</SelectItem>
                              <SelectItem value="admin">Admin</SelectItem>
                              <SelectItem value="super_admin">Super Admin</SelectItem>
                            </SelectContent>
                          </Select>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>
        </main>
        
        {/* Navigation mobile en bas de l'écran */}
        {isMobile && <MobileNavBar />}
      </div>
    </SidebarProvider>
  );
};

export default Admin;
