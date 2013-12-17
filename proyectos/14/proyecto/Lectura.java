import java.io.*;

class Lectura 
{
    public static void main(String[]args)throws IOException 
    {
        BufferedReader lectura = new BufferedReader(new InputStreamReader(System.in));
        String nombre, apellido;
        System.out.println("Ingrese su nombre: ");
        nombre = lectura.readLine();
        System.out.println("Ingrese su apellido: ");
        apellido = lectura.readLine();
        System.out.println("Bienvenido "+nombre+" "+apellido);
    }
}