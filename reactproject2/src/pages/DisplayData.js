import { useEffect, useState } from 'react';
import axios from 'axios'; //
import Header from '../components/Header';
import Nav from '../components/Nav';
import Footer from '../components/Footer';

export default function DisplayData() {
  const [users, setUsers] = useState([]);
//   Fetch() Function use korechi

//     useEffect(() => {
//     fetch('https://jsonplaceholder.typicode.com/users')
//       .then(res => res.json())
//       .then(data => setUsers(data));
   
//   }, []);

  useEffect(() => {
    axios.get('https://jsonplaceholder.typicode.com/users')
      .then(res => {
        setUsers(res.data);
      })
   
  }, []);

  return (
    <>
        <Header />
        <Nav />
        <h1>DisplayData</h1>
        <div className="p-6 max-w-4xl mx-auto">
            <h1 className="text-2xl font-bold mb-4">User List (using Axios)</h1>
            <ul className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                {users.map(user => (
                <li key={user.id} className="bg-white shadow p-4 rounded-xl">
                    <h2 className="text-lg font-semibold">{user.name}</h2>
                    <p className="text-sm text-gray-600">{user.email}</p>
                    <p className="text-sm text-gray-600">{user.company.name}</p>
                </li>
                ))}
            </ul>
        </div>
        <Footer />
    </>
  )
}
