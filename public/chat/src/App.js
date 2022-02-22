import './App.css';
import OnlineUsers from "./components/OnlineUsers/OnlineUsers";
function App() {
   var userList = [
       {username: 'Ala', key: '23232131'}
   ];
    return (
        <div className="App">
            <OnlineUsers users={userList}/>
        </div>
    );
}

export default App;
