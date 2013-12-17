#include <sys/types.h>
#include <sys/types.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdio.h>

int main ( int argc , char * argv []) 
{
    pid_t pid ;
    int i, j, p;
    pid = fork () ;
    
    if ( pid == -1) 
    {
        printf (" Fallo en fork \n ");
        return -1;
    }
    else if (! pid ) 
    {
        printf ("\n ** Proceso hijo iniciado ** \n");
        for (i=0;i<5; i++)
        {
            printf ("\nProceso hijo. Contador %d \n" , i );
            fflush(stdout);
            sleep(1); 
        } 
    }
    else 
    {
        printf ("\n ** Proceso padre iniciado ** \n");
        for (j=0;j<3; j++)
        {
            printf ("\nProceso padre . Contador %d \n " , j ) ;
            fflush(stdout);
            sleep(2); 
        } 
    }

    return 0;
}